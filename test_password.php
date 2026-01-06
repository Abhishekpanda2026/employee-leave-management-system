<?php
// Test script to verify password hashing
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Get the admin user
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "User found:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Password hash: " . $user['password'] . "\n";
        
        // Test if 'admin123' matches the stored hash
        $testPassword = 'admin123';
        $isValid = password_verify($testPassword, $user['password']);
        
        echo "Password verification for 'admin123': " . ($isValid ? 'SUCCESS' : 'FAILED') . "\n";
        
        // If it failed, let's update the password
        if (!$isValid) {
            echo "Updating admin password to 'admin123'...\n";
            $newPasswordHash = password_hash('admin123', PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
            $updateStmt->execute([$newPasswordHash]);
            echo "Password updated successfully.\n";
            
            // Verify the new password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE username = ?");
            $stmt->execute(['admin']);
            $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $newValid = password_verify('admin123', $updatedUser['password']);
            echo "New password verification: " . ($newValid ? 'SUCCESS' : 'FAILED') . "\n";
        }
    } else {
        echo "Admin user not found!\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>