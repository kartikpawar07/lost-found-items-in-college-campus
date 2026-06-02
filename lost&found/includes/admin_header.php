<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

// Route guards: restrict access to logged-in admins
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page !== 'login.php') {
    if (!isset($_SESSION['admin_id'])) {
        $_SESSION['error'] = "Unauthorized access! Please login as administrator.";
        header("Location: " . BASE_URL . "admin/login.php");
        exit();
    } else {
        // Validate admin session integrity (check if admin still exists)
        try {
            $check_admin = $pdo->prepare("SELECT COUNT(*) FROM admin WHERE id = ?");
            $check_admin->execute([$_SESSION['admin_id']]);
            if ($check_admin->fetchColumn() == 0) {
                $_SESSION = array();
                session_destroy();
                session_start();
                $_SESSION['error'] = "Administrator session expired. Please login again.";
                header("Location: " . BASE_URL . "admin/login.php");
                exit();
            }
        } catch (PDOException $e) {
            // DB connection error, let it pass
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " - Admin Panel" : "Admin Panel - Smart Campus"; ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Theme Stylesheet -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body class="bg-light">

    <!-- Admin Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top" style="background: linear-gradient(135deg, #1b262c 0%, #0f171e 100%) !important; border-bottom: 3px solid var(--secondary-color);">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                <i class="bi bi-shield-lock-fill me-1 text-success"></i>CampusAdmin<span>Panel</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php if (isset($_SESSION['admin_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                                <i class="bi bi-speedometer2 me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/users.php">
                                <i class="bi bi-people me-1"></i>Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'lost_items.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/lost_items.php">
                                <i class="bi bi-search me-1"></i>Lost Items
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'found_items.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/found_items.php">
                                <i class="bi bi-gift me-1"></i>Found Items
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'claims.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/claims.php">
                                <i class="bi bi-file-earmark-check me-1"></i>Claims
                                <?php
                                    // Fetch count of pending claims for badge
                                    $stmt = $pdo->query("SELECT COUNT(*) FROM claims WHERE status = 'Pending'");
                                    $pending_count = $stmt->fetchColumn();
                                    if ($pending_count > 0) {
                                        echo "<span class='badge bg-danger ms-1'>$pending_count</span>";
                                    }
                                ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center">
                    <?php if (isset($_SESSION['admin_id'])): ?>
                        <span class="text-white me-3 d-none d-md-inline">
                            <i class="bi bi-person-badge-fill me-1 text-success"></i>Admin Mode: <strong><?php echo sanitize_output($_SESSION['admin_username']); ?></strong>
                        </span>
                        <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Back to Campus site
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
