<?php
/**
 * Leave Balance Logic Documentation and Validation
 * 
 * Rules Implemented:
 * 1. Each employee has:
 *    - 12 Casual Leaves
 *    - 10 Sick Leaves
 * 2. On approval → deduct leave days
 * 3. On rejection → no change
 * 4. Prevent leave if balance is insufficient
 */

// Include database connection
require_once 'config/database.php';

class LeaveBalanceManager {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    /**
     * Check if user has sufficient leave balance for requested days
     */
    public function hasSufficientBalance($userId, $leaveTypeId, $daysRequested) {
        try {
            // Check if balance record exists for this user, type, and year
            $balanceStmt = $this->pdo->prepare("
                SELECT remaining_days 
                FROM leave_balances 
                WHERE user_id = ? AND leave_type_id = ? AND year = YEAR(NOW())
            ");
            $balanceStmt->execute([$userId, $leaveTypeId]);
            $balance = $balanceStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$balance) {
                // If no balance record exists, get the default allocation for the leave type
                $typeStmt = $this->pdo->prepare("SELECT max_days_per_year FROM leave_types WHERE id = ?");
                $typeStmt->execute([$leaveTypeId]);
                $typeInfo = $typeStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($typeInfo) {
                    $remainingDays = $typeInfo['max_days_per_year'];
                } else {
                    return ['success' => false, 'message' => 'Invalid leave type selected.'];
                }
            } else {
                $remainingDays = $balance['remaining_days'];
            }
            
            if ($remainingDays < $daysRequested) {
                return [
                    'success' => false, 
                    'message' => "Insufficient leave balance for the selected period. You have {$remainingDays} days remaining for this leave type."
                ];
            }
            
            return ['success' => true, 'message' => 'Sufficient balance available.'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update leave balance after approval
     */
    public function updateBalanceOnApproval($userId, $leaveTypeId, $daysUsed) {
        try {
            // Check if balance record exists for this user, type, and year
            $balanceStmt = $this->pdo->prepare("
                SELECT id, allocated_days, used_days, remaining_days 
                FROM leave_balances 
                WHERE user_id = ? AND leave_type_id = ? AND year = YEAR(NOW())
            ");
            $balanceStmt->execute([$userId, $leaveTypeId]);
            $balance = $balanceStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($balance) {
                // Update existing balance
                $newUsed = $balance['used_days'] + $daysUsed;
                $newRemaining = $balance['remaining_days'] - $daysUsed;
                
                $updateStmt = $this->pdo->prepare("UPDATE leave_balances SET used_days = ?, remaining_days = ? WHERE id = ?");
                $updateStmt->execute([$newUsed, $newRemaining, $balance['id']]);
                
                return ['success' => true, 'message' => 'Balance updated successfully.'];
            } else {
                // Create new balance record if it doesn't exist
                $typeStmt = $this->pdo->prepare("SELECT max_days_per_year FROM leave_types WHERE id = ?");
                $typeStmt->execute([$leaveTypeId]);
                $typeInfo = $typeStmt->fetch(PDO::FETCH_ASSOC);
                
                $allocated = $typeInfo ? $typeInfo['max_days_per_year'] : 0;
                $used = $daysUsed;
                $remaining = $allocated - $used;
                
                $insertStmt = $this->pdo->prepare("
                    INSERT INTO leave_balances (user_id, leave_type_id, year, allocated_days, used_days, remaining_days) 
                    VALUES (?, ?, YEAR(NOW()), ?, ?, ?)
                ");
                $insertStmt->execute([$userId, $leaveTypeId, $allocated, $used, $remaining]);
                
                return ['success' => true, 'message' => 'New balance record created and updated.'];
            }
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get current leave balance for a user
     */
    public function getUserBalance($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT lt.name as leave_type, lb.allocated_days, lb.used_days, lb.remaining_days
                FROM leave_balances lb
                JOIN leave_types lt ON lb.leave_type_id = lt.id
                WHERE lb.user_id = ? AND lb.year = YEAR(CURDATE())
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}

// Example usage:
echo "<h2>Leave Balance Logic Implementation</h2>\n";
echo "<p>This script demonstrates the leave balance logic:</p>\n";
echo "<ul>\n";
echo "<li>Each employee has 12 Casual Leaves and 10 Sick Leaves</li>\n";
echo "<li>On approval → deduct leave days</li>\n";
echo "<li>On rejection → no change</li>\n";
echo "<li>Prevent leave if balance is insufficient</li>\n";
echo "</ul>\n";

$manager = new LeaveBalanceManager();

// Example: Check if user 2 has sufficient balance for 5 days of Casual Leave (leave_type_id = 3)
$result = $manager->hasSufficientBalance(2, 3, 5);
echo "<h3>Example Validation:</h3>\n";
echo "<p>Checking if user 2 has sufficient balance for 5 days of Casual Leave:</p>\n";
echo "<p><strong>Result:</strong> " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "</p>\n";
echo "<p><strong>Message:</strong> " . $result['message'] . "</p>\n";

// Example: Get user's current balances
$balances = $manager->getUserBalance(2);
echo "<h3>Current Leave Balances for User 2:</h3>\n";
if (!empty($balances)) {
    echo "<table border='1' style='border-collapse: collapse;'>\n";
    echo "<tr><th>Leave Type</th><th>Allocated</th><th>Used</th><th>Remaining</th></tr>\n";
    foreach ($balances as $balance) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($balance['leave_type']) . "</td>";
        echo "<td>" . $balance['allocated_days'] . "</td>";
        echo "<td>" . $balance['used_days'] . "</td>";
        echo "<td>" . $balance['remaining_days'] . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
} else {
    echo "<p>No balance records found for user 2.</p>\n";
}

echo "<h3>Implementation Summary:</h3>\n";
echo "<ul>\n";
echo "<li>Validation occurs when employees submit leave requests</li>\n";
echo "<li>Balance is automatically updated when admin approves a request</li>\n";
echo "<li>Rejection does not affect the leave balance</li>\n";
echo "<li>Prevents employees from applying for leave with insufficient balance</li>\n";
echo "</ul>\n";
?>