<?php
$page_title = "My Found Reports";
require_once __DIR__ . '/../includes/header.php';

// Route guard: check login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to view your reported items.";
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$items = [];

try {
    $stmt = $pdo->prepare("SELECT * FROM found_items WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Database query failed: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="fw-bold text-success mb-0"><i class="bi bi-gift-fill me-2 text-success"></i>My Reported Found Items</h3>
    <a href="<?php echo BASE_URL; ?>found_items/add.php" class="btn btn-success btn-sm">
        <i class="bi bi-plus-circle me-1"></i>Report New Found Item
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
                        <th scope="col">Item Name</th>
                        <th scope="col">Category</th>
                        <th scope="col">Location Found</th>
                        <th scope="col">Date Found</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-center" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-folder2-open display-4 mb-2 d-block"></i>
                                You haven't reported any found items yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
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
                                    <p class="mb-0 text-muted small text-truncate" style="max-width: 250px;"><?php echo sanitize_output($item['description']); ?></p>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo sanitize_output($item['category']); ?></span>
                                </td>
                                <td><?php echo sanitize_output($item['location']); ?></td>
                                <td><?php echo date('d M Y', strtotime($item['found_date'])); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        echo $item['status'] == 'Found' ? 'bg-success' : 'bg-secondary';
                                    ?>"><?php echo sanitize_output($item['status']); ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-primary" title="Edit Report">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-danger" title="Delete Report" onclick="return confirm('Are you sure you want to delete this report? This cannot be undone.');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
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
require_once __DIR__ . '/../includes/footer.php';
?>
