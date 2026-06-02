<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

// If admin is already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: " . BASE_URL . "admin/dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['success'] = "Welcome Administrator! Login successful.";
                header("Location: " . BASE_URL . "admin/dashboard.php");
                exit();
            } else {
                $error = "Invalid administrator username or password.";
            }
        } catch (PDOException $e) {
            $error = "Database query failed: " . $e->getMessage();
        }
    }
}

$page_title = "Admin Login";
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-dark"><i class="bi bi-shield-lock-fill text-danger"></i> Admin Portal</h2>
            <p class="text-muted">Login using administrative credentials</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger shadow-sm py-2">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo sanitize_output($error); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold">Admin Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-shield-fill"></i></span>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($_POST['username']) ? sanitize_output($_POST['username']) : ''; ?>" required>
                    <div class="invalid-feedback">Please enter the admin username.</div>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Admin Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="invalid-feedback">Please enter the admin password.</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 mt-2" style="background-color:#1b262c; border-color:#1b262c;">
                <i class="bi bi-box-arrow-in-right me-2"></i>Admin Sign In
            </button>
        </form>

        <div class="text-center mt-4 border-top pt-3">
            <a href="<?php echo BASE_URL; ?>index.php" class="text-success text-decoration-none fw-semibold">
                <i class="bi bi-arrow-left me-1"></i>Back to Campus Homepage
            </a>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/admin_footer.php';
?>
