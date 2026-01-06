<?php
// Script to initialize leave balances for all users
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Get all users
    $usersStmt = $pdo->query("SELECT id FROM users WHERE role = 'employee'");
    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all leave types
    $typesStmt = $pdo->query("SELECT id, max_days_per_year FROM leave_types WHERE is_active = 1");
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
                // Create new balance record
                $insertStmt = $pdo->prepare("
                    INSERT INTO leave_balances (user_id, leave_type_id, year, allocated_days, used_days, remaining_days) 
                    VALUES (?, ?, ?, ?, 0, ?)
                ");
                $insertStmt->execute([
                    $user['id'], 
                    $type['id'], 
                    $currentYear, 
                    $type['max_days_per_year'], 
                    $type['max_days_per_year']
                ]);
                
                echo "Created balance for user {$user['id']}, type {$type['id']}\n";
            } else {
                echo "Balance already exists for user {$user['id']}, type {$type['id']}\n";
            }
        }
    }
    
    echo "Leave balances initialized successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>