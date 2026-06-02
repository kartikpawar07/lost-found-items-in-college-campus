<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

// Validate session user integrity (prevent orphaned session issues if database is reset or user is deleted)
if (isset($_SESSION['user_id'])) {
    try {
        $check_user = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
        $check_user->execute([$_SESSION['user_id']]);
        if ($check_user->fetchColumn() == 0) {
            // User does not exist, clean up orphaned session and redirect
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
            $_SESSION['error'] = "Your student account no longer exists or the system database was reset. Please register a new account.";
            header("Location: " . BASE_URL . "login.php");
            exit();
        }
    } catch (PDOException $e) {
        // DB connection error, don't block access
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " - Smart Campus Lost & Found" : "Smart Campus Lost & Found System"; ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Theme Stylesheet -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>

    <!-- Responsive Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">
                <i class="bi bi-geo-alt-fill me-1"></i>Campus<span>Lost&Found</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'search.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>search.php">Search Items</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="lostDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Lost Items
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="lostDropdown">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>lost_items/add.php">Report Lost Item</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>lost_items/view.php">My Lost Reports</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="foundDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Found Items
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="foundDropdown">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>found_items/add.php">Report Found Item</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>found_items/view.php">My Found Reports</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'my_claims.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>my_claims.php">My Claims</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="text-white me-3 d-none d-md-inline">
                            <i class="bi bi-person-circle me-1 text-success"></i>Welcome, <strong><?php echo sanitize_output($_SESSION['user_name']); ?></strong>
                        </span>
                        <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>admin/login.php" class="btn btn-outline-danger btn-sm me-2">
                            <i class="bi bi-shield-lock-fill me-1"></i>Admin Portal
                        </a>
                        <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-outline-light btn-sm me-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                        <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-success btn-sm">
                            <i class="bi bi-person-plus-fill me-1"></i>Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Container wrapper -->
    <main class="py-4">
        <div class="container">
            <!-- Alert message block -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
