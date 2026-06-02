<?php
require_once __DIR__ . '/config/database.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Store type of user logging out (admin vs normal user) to redirect properly
$is_admin = isset($_SESSION['admin_id']);

// Clear and destroy session
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

session_start();
$_SESSION['success'] = "You have been logged out successfully.";

if ($is_admin) {
    header("Location: " . BASE_URL . "admin/login.php");
} else {
    header("Location: " . BASE_URL . "login.php");
}
exit();
?>
