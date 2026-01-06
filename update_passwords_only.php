<?php
// Script to update existing user passwords with proper hashing
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Update admin password to 'admin123'
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $result = $stmt->execute([$adminPassword]);
    echo "Admin password updated: " . ($result ? "Success" : "Failed") . "\n";
    
    // Update employee passwords to 'password123'
    $empPassword = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE role = 'employee'");
    $result = $stmt->execute([$empPassword]);
    echo "Employee passwords updated: " . ($result ? "Success" : "Failed") . "\n";
    
    echo "Password update process completed!";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>