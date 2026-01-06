<?php
// Script to fix user data
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Check if admin user already exists
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $checkStmt->execute();
    $adminExists = $checkStmt->fetch();
    
    if (!$adminExists) {
        // Insert admin user with proper password hash
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, employee_id, department, position, role, date_joined, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'admin',
            'admin@gmail.com',
            $adminPassword,
            'System',
            'admin',
            'ADM001',
            'IT',
            'System admin',
            'admin',
            date('Y-m-d'),
            1
        ]);
        
        echo "admin user created successfully!\n";
    } else {
        echo "admin user already exists.\n";
    }
    
    // Check if employee users exist
    $checkEmpStmt = $pdo->prepare("SELECT id FROM users WHERE role = 'employee' LIMIT 1");
    $checkEmpStmt->execute();
    $empExists = $checkEmpStmt->fetch();
    
    if (!$empExists) {
        // Insert employee users
        $users = [
            [
                'username' => 'sagar_rout',
                'email' => 'sagarrout@gmail.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'first_name' => 'sagar',
                'last_name' => 'rout',
                'employee_id' => 'EMP001',
                'department' => 'Engineering',
                'position' => 'Software Engineer',
                'date_joined' => '2024-02-01'
            ],
            [
                'username' => 'babun_panda',
                'email' => 'babunpanda@gmail.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'first_name' => 'babun',
                'last_name' => 'panda',
                'employee_id' => 'EMP002',
                'department' => 'Marketing',
                'position' => 'Marketing Manager',
                'date_joined' => '2025-03-15'
            ],
        ];
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, employee_id, department, position, role, date_joined, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'employee', ?, 1)");
        
        foreach ($users as $user) {
            $stmt->execute([
                $user['username'],
                $user['email'],
                $user['password'],
                $user['first_name'],
                $user['last_name'],
                $user['employee_id'],
                $user['department'],
                $user['position'],
                $user['date_joined']
            ]);
        }
        
        echo "Employee users created successfully!\n";
    } else {
        echo "Employee users already exist.\n";
    }
    
    echo "Database setup completed!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>