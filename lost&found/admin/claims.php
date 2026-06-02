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

// Handle Action (Approve / Reject / Delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $claim_id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($claim_id > 0) {
        try {
            // Fetch claim details first
            $stmt = $pdo->prepare("SELECT * FROM claims WHERE id = ?");
            $stmt->execute([$claim_id]);
            $claim = $stmt->fetch();

            if ($claim) {
                $found_item_id = $claim['found_item_id'];

                if ($action == 'approve') {
                    // Start database transaction for integrity
                    $pdo->beginTransaction();

                    // 1. Approve current claim
                    $up_claim = $pdo->prepare("UPDATE claims SET status = 'Approved' WHERE id = ?");
                    $up_claim->execute([$claim_id]);

                    // 2. Mark found item as Claimed
                    $up_item = $pdo->prepare("UPDATE found_items SET status = 'Claimed' WHERE id = ?");
                    $up_item->execute([$found_item_id]);

                    // 3. Auto-reject other pending claims for this specific found item
                    $reject_others = $pdo->prepare("UPDATE claims SET status = 'Rejected' WHERE found_item_id = ? AND id != ? AND status = 'Pending'");
                    $reject_others->execute([$found_item_id, $claim_id]);

                    $pdo->commit();
                    $_SESSION['success'] = "Claim request approved. Found item status set to 'Claimed' and competing pending claims auto-rejected.";

                } elseif ($action == 'reject') {
                    // Reject current claim
                    $up_claim = $pdo->prepare("UPDATE claims SET status = 'Rejected' WHERE id = ?");
                    $up_claim->execute([$claim_id]);

                    $_SESSION['success'] = "Claim request has been rejected.";

                } elseif ($action == 'delete') {
                    // Delete the claim record
                    $del_claim = $pdo->prepare("DELETE FROM claims WHERE id = ?");
                    $del_claim->execute([$claim_id]);

                    $_SESSION['success'] = "Claim record deleted from database.";
                }
            } else {
                $_SESSION['error'] = "Claim record not found.";
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['error'] = "Action failed: " . $e->getMessage();
        }
    }
    header("Location: claims.php");
    exit();
}

$claims = [];

try {
    $stmt = $pdo->query("
        SELECT c.*, 
               u.name as claimant_name, u.email as claimant_email, u.phone as claimant_phone, u.department as claimant_dept,
               fi.item_name, fi.category, fi.location as found_location, fi.found_date, fi.status as item_status
        FROM claims c
        JOIN users u ON c.user_id = u.id
        JOIN found_items fi ON c.found_item_id = fi.id
        ORDER BY c.id DESC
    ");
    $claims = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Database query failed: " . $e->getMessage();
}

$page_title = "Manage Claims";
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-file-earmark-check-fill me-2 text-warning"></i>Manage Claim Requests</h3>
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
                        <th scope="col" style="width: 110px;">Claim ID</th>
                        <th scope="col">Claimant Student</th>
                        <th scope="col">Found Item Details</th>
                        <th scope="col">Claim Proof / Reason</th>
                        <th scope="col">Claimed Date</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-center" style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($claims)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-check display-4 mb-2 d-block"></i>
                                No claim requests submitted yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($claims as $cl): ?>
                            <tr class="<?php echo $cl['status'] == 'Pending' ? 'table-warning-subtle' : ''; ?>">
                                <td class="fw-semibold text-secondary align-middle"><?php echo get_claim_uid($cl['id']); ?></td>
                                <td>
                                    <strong><?php echo sanitize_output($cl['claimant_name']); ?></strong>
                                    <br><span class="small text-muted"><?php echo sanitize_output($cl['claimant_dept']); ?></span>
                                    <br><span class="small text-muted"><i class="bi bi-telephone text-primary small"></i> <?php echo sanitize_output($cl['claimant_phone']); ?></span>
                                </td>
                                <td>
                                    <strong><?php echo sanitize_output($cl['item_name']); ?></strong>
                                    <br><span class="badge bg-light text-secondary border small mt-1">Item ID: <?php echo get_found_uid($cl['found_item_id']); ?></span>
                                    <br><span class="badge bg-secondary"><?php echo sanitize_output($cl['category']); ?></span>
                                    <br><span class="small text-muted"><i class="bi bi-geo-alt"></i> Found at: <?php echo sanitize_output($cl['found_location']); ?></span>
                                </td>
                                <td>
                                    <div class="small">
                                        <strong>Reason:</strong>
                                        <p class="mb-1 text-dark text-wrap" style="max-width: 300px;"><?php echo sanitize_output($cl['claim_reason']); ?></p>
                                        <?php if (!empty($cl['additional_info'])): ?>
                                            <strong>Additional Info:</strong>
                                            <p class="mb-0 text-muted text-wrap" style="max-width: 300px;"><?php echo sanitize_output($cl['additional_info']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo date('d M Y, h:i A', strtotime($cl['created_at'])); ?></td>
                                <td>
                                    <?php 
                                        $badge_class = 'bg-secondary';
                                        if ($cl['status'] == 'Pending') {
                                            $badge_class = 'bg-warning text-dark';
                                        } elseif ($cl['status'] == 'Approved') {
                                            $badge_class = 'bg-success';
                                        } elseif ($cl['status'] == 'Rejected') {
                                            $badge_class = 'bg-danger';
                                        }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo sanitize_output($cl['status']); ?></span>
                                    <?php if ($cl['status'] == 'Approved' && $cl['item_status'] == 'Claimed'): ?>
                                        <br><span class="badge bg-light text-success border mt-1">Item Claimed</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($cl['status'] == 'Pending'): ?>
                                        <a href="claims.php?action=approve&id=<?php echo $cl['id']; ?>" class="btn btn-success btn-sm me-1" title="Approve Claim" onclick="return confirm('Are you sure you want to approve this claim? This will mark the item as Claimed and reject all other competing pending claims.');">
                                            <i class="bi bi-check-circle-fill"></i> Approve
                                        </a>
                                        <a href="claims.php?action=reject&id=<?php echo $cl['id']; ?>" class="btn btn-warning btn-sm text-dark me-1" title="Reject Claim" onclick="return confirm('Are you sure you want to reject this claim?');">
                                            <i class="bi bi-x-circle-fill"></i> Reject
                                        </a>
                                    <?php endif; ?>
                                    <a href="claims.php?action=delete&id=<?php echo $cl['id']; ?>" class="btn btn-outline-danger btn-sm" title="Delete Claim Record" onclick="return confirm('Are you sure you want to delete this claim record?');">
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
