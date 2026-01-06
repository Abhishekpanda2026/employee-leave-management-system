<?php
// Start session with security settings
if (session_status() == PHP_SESSION_NONE) {
    // Configure session for security
    ini_set('session.cookie_httponly', 1);  // Prevent XSS
    ini_set('session.cookie_secure', 0);   // Set to 1 if using HTTPS
    ini_set('session.use_strict_mode', 1); // Prevent session fixation
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Check user role
function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

// Check if user is admin
function isAdmin() {
    return getUserRole() === 'admin';
}

// Check if user is employee
function isEmployee() {
    return getUserRole() === 'employee';
}

// Redirect based on role
function redirectBasedOnRole() {
    if (isAdmin()) {
        header("Location: ../admin/dashboard.php");
        exit();
    } elseif (isEmployee()) {
        header("Location: ../employee/dashboard.php");
        exit();
    } else {
        header("Location: ../login.php");
        exit();
    }
}

// Check if user has permission to access a page
function checkPermission($requiredRole = null) {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
    
    if ($requiredRole && getUserRole() !== $requiredRole) {
        header("Location: ../index.php");
        exit();
    }
}

// Get current user info
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => (int)$_SESSION['user_id'],
            'username' => htmlspecialchars($_SESSION['username']),
            'role' => htmlspecialchars($_SESSION['role']),
            'first_name' => htmlspecialchars($_SESSION['first_name']),
            'last_name' => htmlspecialchars($_SESSION['last_name'])
        ];
    }
    return null;
}

// Update user session with additional information
function updateUserSession($userId) {
    // Use absolute path based on the project root
    require_once __DIR__ . '/../config/database.php';
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT employee_id, department, position, date_joined FROM users WHERE id = ?");
        $stmt->execute([(int)$userId]);
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userInfo) {
            $_SESSION['employee_id'] = htmlspecialchars($userInfo['employee_id']);
            $_SESSION['department'] = htmlspecialchars($userInfo['department']);
            $_SESSION['position'] = htmlspecialchars($userInfo['position']);
            $_SESSION['date_joined'] = $userInfo['date_joined'];
        }
    } catch (PDOException $e) {
        // Silently fail if there's an error
    }
}

// Validate user input
function validateInput($data) {
    return trim(htmlspecialchars(strip_tags($data)));
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate date
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
?>