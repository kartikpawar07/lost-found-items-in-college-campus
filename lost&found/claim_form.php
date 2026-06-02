<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/database.php';

// Route guard: check login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to submit a claim request.";
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$found_item_id = isset($_GET['found_id']) ? (int)$_GET['found_id'] : 0;

if ($found_item_id <= 0) {
    $_SESSION['error'] = "Invalid item selection.";
    header("Location: " . BASE_URL . "search.php");
    exit();
}

// Fetch found item details to verify availability
try {
    $stmt = $pdo->prepare("SELECT * FROM found_items WHERE id = ?");
    $stmt->execute([$found_item_id]);
    $item = $stmt->fetch();

    if (!$item) {
        $_SESSION['error'] = "Item not found.";
        header("Location: " . BASE_URL . "search.php");
        exit();
    }

    if ($item['status'] == 'Claimed') {
        $_SESSION['error'] = "This item has already been claimed.";
        header("Location: " . BASE_URL . "search.php");
        exit();
    }

    // Security Check: student cannot claim their own found item report
    if ($item['user_id'] == $user_id) {
        $_SESSION['error'] = "You cannot file a claim request for an item you reported as found yourself.";
        header("Location: " . BASE_URL . "search.php");
        exit();
    }

    // Check if user has already claimed this item
    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM claims WHERE user_id = ? AND found_item_id = ?");
    $check_stmt->execute([$user_id, $found_item_id]);
    if ($check_stmt->fetchColumn() > 0) {
        $_SESSION['error'] = "You have already submitted a claim request for this item.";
        header("Location: " . BASE_URL . "my_claims.php");
        exit();
    }

} catch (PDOException $e) {
    $_SESSION['error'] = "Database lookup failed: " . $e->getMessage();
    header("Location: " . BASE_URL . "search.php");
    exit();
}

$errors = [];
$claim_reason = $additional_info = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $claim_reason = trim($_POST['claim_reason']);
    $additional_info = trim($_POST['additional_info']);

    if (empty($claim_reason)) {
        $errors[] = "Please provide a reason for claiming this item.";
    }

    if (empty($errors)) {
        try {
            $ins_stmt = $pdo->prepare("INSERT INTO claims (user_id, found_item_id, claim_reason, additional_info, status) VALUES (?, ?, ?, ?, 'Pending')");
            $ins_stmt->execute([$user_id, $found_item_id, $claim_reason, $additional_info]);

            $_SESSION['success'] = "Claim request submitted successfully! The administrator will review your claim.";
            header("Location: " . BASE_URL . "my_claims.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Failed to submit claim request: " . $e->getMessage();
        }
    }
}

$page_title = "Submit Claim Request";
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-check-fill me-2"></i>Claim Found Item</h5>
            </div>
            <div class="card-body p-4">
                <!-- Item Info summary card -->
                <div class="card bg-light border-0 mb-4">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded bg-white d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; overflow: hidden; border: 1px solid #dee2e6;">
                            <?php if ($item['image']): ?>
                                <img src="<?php echo BASE_URL . 'uploads/' . sanitize_output($item['image']); ?>" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <i class="bi bi-image text-muted display-6"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1 text-dark"><?php echo sanitize_output($item['item_name']); ?> <span class="text-secondary font-monospace small">(<?php echo get_found_uid($item['id']); ?>)</span></h5>
                            <span class="badge bg-secondary mb-1"><?php echo sanitize_output($item['category']); ?></span>
                            <div class="small text-muted">
                                <span><i class="bi bi-geo-alt me-1"></i>Found at: <?php echo sanitize_output($item['location']); ?></span> | 
                                <span><i class="bi bi-calendar-event me-1"></i>Date: <?php echo date('d M Y', strtotime($item['found_date'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm py-2">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo sanitize_output($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="claim_reason" class="form-label fw-semibold">Why are you claiming this item? <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="claim_reason" name="claim_reason" rows="4" placeholder="e.g. I lost my wallet in the library matching this description; it contains my ID card ending with 456..." required><?php echo sanitize_output($claim_reason); ?></textarea>
                        <div class="invalid-feedback">Please enter the reasons for your claim.</div>
                        <div class="form-text">Provide enough details to identify the item as yours (e.g. contents of a bag, color details not mentioned, lock screen wallpaper, etc.).</div>
                    </div>

                    <div class="mb-4">
                        <label for="additional_info" class="form-label fw-semibold">Additional Verification Details <span class="text-muted">(Optional)</span></label>
                        <textarea class="form-control" id="additional_info" name="additional_info" rows="3" placeholder="e.g. Any contact details, proof of purchase details, or secondary proof..."><?php echo sanitize_output($additional_info); ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?php echo BASE_URL; ?>search.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-check-circle-fill me-1"></i>Submit Claim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
