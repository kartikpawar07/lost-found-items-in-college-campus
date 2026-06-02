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
$success = '';
$step = 1; // Step 1: Verification, Step 2: Reset Password

if (isset($_SESSION['reset_verified_email'])) {
    $step = 2;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'verify') {
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $department = trim($_POST['department']);

        if (empty($email) || empty($phone) || empty($department)) {
            $error = "All fields are required for verification.";
        } else {
            try {
                // Verify student details
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND phone = ? AND department = ?");
                $stmt->execute([$email, $phone, $department]);
                $user = $stmt->fetch();

                if ($user) {
                    $_SESSION['reset_verified_email'] = $user['email'];
                    $step = 2;
                } else {
                    $error = "No matching student record found. Please verify details.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'reset') {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $email = $_SESSION['reset_verified_email'] ?? '';

        if (empty($email)) {
            $error = "Session expired. Please verify details again.";
            $step = 1;
        } elseif (empty($password) || empty($confirm_password)) {
            $error = "Password fields cannot be empty.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            try {
                // Update password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$hashed_password, $email]);

                unset($_SESSION['reset_verified_email']);
                $_SESSION['success'] = "Password reset successful! You can now login.";
                header("Location: " . BASE_URL . "login.php");
                exit();
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

$page_title = "Forgot Password";
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <?php if ($step == 1): ?>
            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary"><i class="bi bi-shield-lock-fill text-success"></i> Recover</h2>
                <p class="text-muted">Verify details to recover your account</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger shadow-sm py-2">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo sanitize_output($error); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="verify">
                
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? sanitize_output($_POST['email']) : ''; ?>" required>
                    <div class="invalid-feedback">Please enter your registered email.</div>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label fw-semibold">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? sanitize_output($_POST['phone']) : ''; ?>" required>
                    <div class="invalid-feedback">Please enter your registered phone number.</div>
                </div>

                <div class="mb-3">
                    <label for="department" class="form-label fw-semibold">Department</label>
                    <select class="form-select" id="department" name="department" required>
                        <option value="" disabled selected>Select Department</option>
                        <option value="Computer Engineering">Computer Engineering</option>
                        <option value="Information Technology">Information Technology</option>
                        <option value="Electrical Engineering">Electrical Engineering</option>
                        <option value="Mechanical Engineering">Mechanical Engineering</option>
                        <option value="Civil Engineering">Civil Engineering</option>
                        <option value="Business Administration">Business Administration</option>
                        <option value="Science & Humanities">Science & Humanities</option>
                        <option value="Others">Others</option>
                    </select>
                    <div class="invalid-feedback">Please choose your department.</div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 mt-2">
                    <i class="bi bi-patch-check-fill me-2"></i>Verify Details
                </button>
            </form>

        <?php else: ?>
            <div class="text-center mb-4">
                <h2 class="fw-bold text-success"><i class="bi bi-key-fill text-primary"></i> Reset Password</h2>
                <p class="text-muted">Enter a new secure password for: <br><strong><?php echo sanitize_output($email); ?></strong></p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger shadow-sm py-2">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo sanitize_output($error); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="needs-validation-register" novalidate>
                <input type="hidden" name="action" value="reset">

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">New Password</label>
                    <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                    <div class="invalid-feedback">Password must be at least 6 characters.</div>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" required>
                    <div class="invalid-feedback">Please re-enter your password.</div>
                </div>

                <button type="submit" class="btn btn-success w-100 py-2 mt-2">
                    <i class="bi bi-arrow-repeat me-2"></i>Change Password
                </button>
            </form>
        <?php endif; ?>

        <div class="text-center mt-4 border-top pt-3">
            <a href="login.php" class="text-primary text-decoration-none fw-semibold"><i class="bi bi-arrow-left me-1"></i>Back to Login</a>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
