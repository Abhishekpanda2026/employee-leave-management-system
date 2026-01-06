<?php
// Test script to verify login functionality
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Test if we can retrieve users
    $stmt = $pdo->query("SELECT id, username, role FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Users in the database:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Username</th><th>Role</th></tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><h3>Login Test Credentials:</h3>";
    echo "<p><strong>admin:</strong> Username: admin, Password: admin123</p>";
    echo "<p><strong>Employee:</strong> Username: john_doe, Password: password123 (or any employee username)</p>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>