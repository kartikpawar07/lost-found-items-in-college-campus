<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/database.php';

// If student is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            // Find student in DB
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session details
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['success'] = "Welcome back, " . $user['name'] . "! Login successful.";
                header("Location: " . BASE_URL . "dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

$page_title = "Student Login";
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary"><i class="bi bi-geo-alt-fill text-success"></i> Login</h2>
            <p class="text-muted">Access your Smart Campus dashboard</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger shadow-sm py-2">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo sanitize_output($error); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Campus Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? sanitize_output($_POST['email']) : ''; ?>" required>
                    <div class="invalid-feedback">Please enter a valid college email.</div>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <a href="forgot_password.php" class="text-decoration-none small text-success">Forgot Password?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="invalid-feedback">Please enter your password.</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 mt-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <div class="text-center mt-4 border-top pt-3">
            <p class="text-muted mb-0">Don't have an account? <a href="register.php" class="text-success text-decoration-none fw-semibold">Register here</a></p>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
