<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/database.php';

// Route guard: check login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to view your claim requests.";
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$claims = [];

try {
    $stmt = $pdo->prepare("
        SELECT c.*, fi.item_name, fi.category, fi.location, fi.image 
        FROM claims c 
        JOIN found_items fi ON c.found_item_id = fi.id 
        WHERE c.user_id = ? 
        ORDER BY c.id DESC
    ");
    $stmt->execute([$user_id]);
    $claims = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Database query failed: " . $e->getMessage();
}

$page_title = "My Claims";
require_once __DIR__ . '/includes/header.php';
?>

<h3 class="fw-bold text-primary mb-4"><i class="bi bi-file-earmark-check-fill me-2 text-warning"></i>My Submitted Claim Requests</h3>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th scope="col" style="width: 80px;">Image</th>
                        <th scope="col" style="width: 110px;">Claim ID</th>
                        <th scope="col">Found Item Name</th>
                        <th scope="col">Category</th>
                        <th scope="col">Date Filed</th>
                        <th scope="col">Claim Reason</th>
                        <th scope="col">Claim Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($claims)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-lock2 display-4 mb-2 d-block"></i>
                                You haven't submitted any claim requests yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($claims as $claim): ?>
                            <tr>
                                <td>
                                    <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; overflow: hidden; border: 1px solid #dee2e6;">
                                        <?php if ($claim['image']): ?>
                                            <img src="<?php echo BASE_URL . 'uploads/' . sanitize_output($claim['image']); ?>" style="width:100%; height:100%; object-fit:cover;">
                                        <?php else: ?>
                                            <i class="bi bi-image text-muted"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="fw-semibold text-secondary align-middle"><?php echo get_claim_uid($claim['id']); ?></td>
                                <td>
                                    <strong class="text-dark"><?php echo sanitize_output($claim['item_name']); ?></strong>
                                    <span class="badge bg-light text-secondary border small ms-1">Item ID: <?php echo get_found_uid($claim['found_item_id']); ?></span>
                                    <p class="mb-0 text-muted small"><i class="bi bi-geo-alt me-1"></i>Found at: <?php echo sanitize_output($claim['location']); ?></p>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo sanitize_output($claim['category']); ?></span>
                                </td>
                                <td><?php echo date('d M Y, h:i A', strtotime($claim['created_at'])); ?></td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 300px;" title="<?php echo sanitize_output($claim['claim_reason']); ?>">
                                        <?php echo sanitize_output($claim['claim_reason']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        $badge_class = 'bg-secondary';
                                        if ($claim['status'] == 'Pending') {
                                            $badge_class = 'bg-warning text-dark';
                                        } elseif ($claim['status'] == 'Approved') {
                                            $badge_class = 'bg-success';
                                        } elseif ($claim['status'] == 'Rejected') {
                                            $badge_class = 'bg-danger';
                                        }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo sanitize_output($claim['status']); ?></span>
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
require_once __DIR__ . '/includes/footer.php';
?>
