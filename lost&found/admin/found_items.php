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

// Handle item deletion
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    if ($delete_id > 0) {
        try {
            // Fetch image path to delete file
            $stmt = $pdo->prepare("SELECT image FROM found_items WHERE id = ?");
            $stmt->execute([$delete_id]);
            $image = $stmt->fetchColumn();

            if ($image) {
                $image_path = __DIR__ . '/../uploads/' . $image;
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }

            // Delete item record
            $del_stmt = $pdo->prepare("DELETE FROM found_items WHERE id = ?");
            $del_stmt->execute([$delete_id]);

            $_SESSION['success'] = "Found item record deleted successfully.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Failed to delete found item: " . $e->getMessage();
        }
    }
    header("Location: found_items.php");
    exit();
}

$found_items = [];

try {
    $stmt = $pdo->query("
        SELECT fi.*, u.name as finder_name, u.email as finder_email 
        FROM found_items fi 
        JOIN users u ON fi.user_id = u.id 
        ORDER BY fi.id DESC
    ");
    $found_items = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Database query failed: " . $e->getMessage();
}

$page_title = "Manage Found Items";
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-gift-fill me-2 text-success"></i>Manage Found Item Reports</h3>
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
                        <th scope="col" style="width: 80px;">Image</th>
                        <th scope="col" style="width: 120px;">Report ID</th>
                        <th scope="col">Item Details</th>
                        <th scope="col">Category</th>
                        <th scope="col">Finder Student</th>
                        <th scope="col">Found Location</th>
                        <th scope="col">Date Found</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-center" style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($found_items)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-gift display-4 mb-2 d-block"></i>
                                No found items reported yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($found_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; overflow: hidden; border: 1px solid #dee2e6;">
                                        <?php if ($item['image']): ?>
                                            <img src="<?php echo BASE_URL . 'uploads/' . sanitize_output($item['image']); ?>" style="width:100%; height:100%; object-fit:cover;">
                                        <?php else: ?>
                                            <i class="bi bi-image text-muted"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="fw-semibold text-secondary align-middle"><?php echo get_found_uid($item['id']); ?></td>
                                <td>
                                    <strong class="text-dark"><?php echo sanitize_output($item['item_name']); ?></strong>
                                    <p class="mb-0 text-muted small text-truncate" style="max-width: 200px;"><?php echo sanitize_output($item['description']); ?></p>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo sanitize_output($item['category']); ?></span>
                                </td>
                                <td>
                                    <strong><?php echo sanitize_output($item['finder_name']); ?></strong>
                                    <br><span class="small text-muted"><?php echo sanitize_output($item['finder_email']); ?></span>
                                </td>
                                <td><?php echo sanitize_output($item['location']); ?></td>
                                <td><?php echo date('d M Y', strtotime($item['found_date'])); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        echo $item['status'] == 'Found' ? 'bg-success' : 'bg-secondary';
                                    ?>"><?php echo sanitize_output($item['status']); ?></span>
                                </td>
                                <td class="text-center">
                                    <a href="found_items.php?delete_id=<?php echo $item['id']; ?>" class="btn btn-outline-danger btn-sm" title="Delete Report" onclick="return confirm('Are you sure you want to delete this report? This will remove it permanently.');">
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
