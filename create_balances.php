<?php
// Script to create initial leave balances
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Get all users
    $usersStmt = $pdo->query("SELECT id FROM users WHERE role = 'employee'");
    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all leave types
    $typesStmt = $pdo->query("SELECT id, max_days_per_year FROM leave_types");
    $leaveTypes = $typesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $currentYear = date('Y');
    
    foreach ($users as $user) {
        foreach ($leaveTypes as $type) {
            // Check if balance record already exists for this user, type, and year
            $checkStmt = $pdo->prepare("
                SELECT id FROM leave_balances 
                WHERE user_id = ? AND leave_type_id = ? AND year = ?
            ");
            $checkStmt->execute([$user['id'], $type['id'], $currentYear]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing) {
                // Create new balance record with full allocation
                $allocated = $type['max_days_per_year'];
                $used = 0;
                $remaining = $allocated;
                
                $insertStmt = $pdo->prepare("
                    INSERT INTO leave_balances (user_id, leave_type_id, year, allocated_days, used_days, remaining_days) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $insertStmt->execute([
                    $user['id'], 
                    $type['id'], 
                    $currentYear, 
                    $allocated, 
                    $used, 
                    $remaining
                ]);
                
                echo "Created balance for user {$user['id']}, type {$type['id']}: {$allocated} days\n";
            } else {
                echo "Balance already exists for user {$user['id']}, type {$type['id']}\n";
            }
        }
    }
    
    echo "Leave balances setup completed!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>