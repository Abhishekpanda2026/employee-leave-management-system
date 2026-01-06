<!-- <?php
session_start();
require_once "config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $sql = "SELECT id, username, password, role 
            FROM users 
            WHERE username = ? AND is_active = 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["role"] = $user["role"];

        if ($user["role"] === "admin") {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: employee/dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?> -->
