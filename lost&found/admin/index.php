<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Automatically route direct folder access (e.g. localhost/lost&found/admin/)
if (isset($_SESSION['admin_id'])) {
    header("Location: " . BASE_URL . "admin/dashboard.php");
} else {
    header("Location: " . BASE_URL . "admin/login.php");
}
exit();
?>
