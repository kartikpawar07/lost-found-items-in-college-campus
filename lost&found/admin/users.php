<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

// Route guards: restrict access to logged-in admins
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access! Please login as administrator.";
    header("Location: " . BASE_URL . "admin/login.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    if ($delete_id > 0) {
        try {
            // Fetch student name for feedback
            $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
            $stmt->execute([$delete_id]);
            $student_name = $stmt->fetchColumn();

            if ($student_name) {
                // Delete student. The DB will ON DELETE CASCADE to clear their lost_items, found_items, and claims!
                $del_stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $del_stmt->execute([$delete_id]);

                $_SESSION['success'] = "Student account '{$student_name}' and all associated records deleted successfully.";
            } else {
                $_SESSION['error'] = "Student record not found.";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Failed to delete student: " . $e->getMessage();
        }
    }
    header("Location: users.php");
    exit();
}

$students = [];

try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY name ASC");
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Database query failed: " . $e->getMessage();
}

$page_title = "Manage Students";
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-people me-2 text-primary"></i>Manage Registered Students</h3>
    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th scope="col" style="width: 60px;">ID</th>
                        <th scope="col">Full Name</th>
                        <th scope="col">Department</th>
                        <th scope="col">Email Address</th>
                        <th scope="col">Phone Number</th>
                        <th scope="col">Registration Date</th>
                        <th scope="col" class="text-center" style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-people display-4 mb-2 d-block"></i>
                                No student accounts registered yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $stu): ?>
                            <tr>
                                <td><?php echo $stu['id']; ?></td>
                                <td><strong class="text-dark"><?php echo sanitize_output($stu['name']); ?></strong></td>
                                <td><span class="badge bg-secondary"><?php echo sanitize_output($stu['department']); ?></span></td>
                                <td><a href="mailto:<?php echo $stu['email']; ?>"><?php echo sanitize_output($stu['email']); ?></a></td>
                                <td><?php echo sanitize_output($stu['phone']); ?></td>
                                <td><?php echo date('d M Y, h:i A', strtotime($stu['created_at'])); ?></td>
                                <td class="text-center">
                                    <a href="users.php?delete_id=<?php echo $stu['id']; ?>" class="btn btn-outline-danger btn-sm" title="Delete Student" onclick="return confirm('Are you sure you want to delete this student account? All their lost/found reports and claim requests will be deleted permanently!');">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/admin_footer.php';
?>
