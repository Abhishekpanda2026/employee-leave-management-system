<?php
// Include database connection and auth functions
require_once 'config/database.php';
require_once 'includes/auth.php';

// If user is already logged in, redirect based on role
if (isLoggedIn()) {
    redirectBasedOnRole();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $username = validateInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        try {
            $pdo = getDBConnection();
            // Use prepared statement to prevent SQL injection
            $stmt = $pdo->prepare("SELECT id, username, password, role, first_name, last_name FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables with security measures
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['logged_in'] = true; // Security flag
                
                // Update session with additional user information
                updateUserSession($user['id']);
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: employee/dashboard.php");
                }
                exit();
            } else {
                $error_message = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $error_message = "Database error occurred.";
        }
    } else {
        $error_message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Employee Leave Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/login.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card mt-5 shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3 class="mb-0">Leave Management System</h3>
                        <p class="mb-0">Employee Portal</p>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="loginForm">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username or Email</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                <div class="invalid-feedback">Please enter your username or email.</div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="invalid-feedback">Please enter your password.</div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            let isValid = true;
            
            // Validate username
            if (username === '') {
                document.getElementById('username').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('username').classList.remove('is-invalid');
            }
            
            // Validate password
            if (password === '') {
                document.getElementById('password').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('password').classList.remove('is-invalid');
            }
            
            if (!isValid) {
                e.preventDefault();
                return false;
            }
        });
        
        // Clear validation on input
        document.getElementById('username').addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
        
        document.getElementById('password').addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    </script>
</body>
</html>