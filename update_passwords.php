<?php
// Script to update passwords with proper hashing
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Update admin password to 'admin123'
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$adminPassword]);
    
    // Update employee passwords to 'password123'
    $empPassword = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE role = 'employee'");
    $stmt->execute([$empPassword]);
    
    echo "Passwords updated successfully!";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>