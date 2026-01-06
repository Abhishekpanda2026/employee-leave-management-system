<?php
// Include auth middleware
require_once 'includes/auth.php';

// Redirect based on login status
if (isLoggedIn()) {
    redirectBasedOnRole();
} else {
    header("Location: login.php");
    exit();
}
?>