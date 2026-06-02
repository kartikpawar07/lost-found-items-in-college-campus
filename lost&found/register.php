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

$errors = [];
$name = $email = $phone = $department = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $department = trim($_POST['department']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Basic empty validations
    if (empty($name)) $errors[] = "Full Name is required.";
    if (empty($email)) $errors[] = "Email Address is required.";
    if (empty($phone)) $errors[] = "Phone Number is required.";
    if (empty($department)) $errors[] = "Department selection is required.";
    if (empty($password)) $errors[] = "Password is required.";

    // 2. Format validations
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (!empty($phone) && !preg_match('/^[0-9]{10,15}$/', $phone)) {
        $errors[] = "Phone number must be between 10 to 15 digits.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // 3. Database validation (Check email uniqueness)
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "This email is already registered.";
            } else {
                // Secure password hash
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Save user to database
                $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, department, password) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $phone, $department, $hashed_password]);

                $_SESSION['success'] = "Registration successful! You can now login.";
                header("Location: " . BASE_URL . "login.php");
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

$page_title = "Student Registration";
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 580px;">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary"><i class="bi bi-person-plus-fill text-success"></i> Register</h2>
            <p class="text-muted">Create a student account for Smart Campus access</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger shadow-sm py-2">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo sanitize_output($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="needs-validation-register" novalidate>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label fw-semibold">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo sanitize_output($name); ?>" required>
                    <div class="invalid-feedback">Please enter your full name.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label fw-semibold">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo sanitize_output($email); ?>" required>
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label fw-semibold">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo sanitize_output($phone); ?>" required>
                    <div class="invalid-feedback">Please enter 10 to 15 digits phone number.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="department" class="form-label fw-semibold">Department</label>
                    <select class="form-select" id="department" name="department" required>
                        <option value="" disabled <?php echo empty($department) ? 'selected' : ''; ?>>Select Department</option>
                        <option value="Computer Engineering" <?php echo $department == 'Computer Engineering' ? 'selected' : ''; ?>>Computer Engineering</option>
                        <option value="Information Technology" <?php echo $department == 'Information Technology' ? 'selected' : ''; ?>>Information Technology</option>
                        <option value="Electrical Engineering" <?php echo $department == 'Electrical Engineering' ? 'selected' : ''; ?>>Electrical Engineering</option>
                        <option value="Mechanical Engineering" <?php echo $department == 'Mechanical Engineering' ? 'selected' : ''; ?>>Mechanical Engineering</option>
                        <option value="Civil Engineering" <?php echo $department == 'Civil Engineering' ? 'selected' : ''; ?>>Civil Engineering</option>
                        <option value="Business Administration" <?php echo $department == 'Business Administration' ? 'selected' : ''; ?>>Business Administration</option>
                        <option value="Science & Humanities" <?php echo $department == 'Science & Humanities' ? 'selected' : ''; ?>>Science & Humanities</option>
                        <option value="Others" <?php echo $department == 'Others' ? 'selected' : ''; ?>>Others</option>
                    </select>
                    <div class="invalid-feedback">Please choose a department.</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                    <div class="invalid-feedback">Password must be at least 6 characters.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" required>
                    <div class="invalid-feedback">Please re-enter your password.</div>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100 py-2 mt-3">
                <i class="bi bi-person-plus-fill me-2"></i>Register
            </button>
        </form>

        <div class="text-center mt-4 border-top pt-3">
            <p class="text-muted mb-0">Already registered? <a href="login.php" class="text-primary text-decoration-none fw-semibold">Login here</a></p>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
