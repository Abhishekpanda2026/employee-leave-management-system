<?php
// Script to add remaining employees
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Hashed password for all employees (same as before)
    $password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    // Add remaining employees
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, employee_id, department, position, role, date_joined) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'employee', ?)");
    
    $employees = [
        ['robert_wilson', 'robert.wilson@company.com', $password, 'Robert', 'Wilson', 'EMP003', 'HR', 'HR Specialist', '2023-01-10'],
        ['sarah_johnson', 'sarah.johnson@company.com', $password, 'Sarah', 'Johnson', 'EMP004', 'Finance', 'Accountant', '2023-04-20']
    ];
    
    foreach ($employees as $emp) {
        $stmt->execute($emp);
    }
    
    echo "Successfully added remaining employees!";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>