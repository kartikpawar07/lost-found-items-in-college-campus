<?php
$page_title = "Student Dashboard";
require_once __DIR__ . '/includes/header.php';

// Route guard: check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to access your dashboard.";
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch personal profile details
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_profile = $stmt->fetch();
} catch (PDOException $e) {
    $user_profile = ['department' => 'Unknown', 'phone' => 'N/A'];
}

// Fetch stats for this student
try {
    // 1. Total Lost Items reported by user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM lost_items WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $my_lost_count = $stmt->fetchColumn();

    // 2. Total Found Items reported by user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM found_items WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $my_found_count = $stmt->fetchColumn();

    // 3. Total claims submitted by user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM claims WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $my_claims_count = $stmt->fetchColumn();

    // Fetch recent lost items (latest 3)
    $stmt = $pdo->prepare("SELECT * FROM lost_items WHERE user_id = ? ORDER BY id DESC LIMIT 3");
    $stmt->execute([$user_id]);
    $recent_lost = $stmt->fetchAll();

    // Fetch recent found items (latest 3)
    $stmt = $pdo->prepare("SELECT * FROM found_items WHERE user_id = ? ORDER BY id DESC LIMIT 3");
    $stmt->execute([$user_id]);
    $recent_found = $stmt->fetchAll();

} catch (PDOException $e) {
    $my_lost_count = $my_found_count = $my_claims_count = 0;
    $recent_lost = $recent_found = [];
}
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-primary text-white border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h2 class="fw-bold mb-1"><i class="bi bi-hand-wave-fill me-2 text-warning"></i>Welcome, <?php echo sanitize_output($_SESSION['user_name']); ?>!</h2>
                        <p class="mb-0 opacity-85">
                            <i class="bi bi-building me-1"></i>Department: <strong><?php echo sanitize_output($user_profile['department']); ?></strong> | 
                            <i class="bi bi-telephone-fill me-1"></i>Phone: <strong><?php echo sanitize_output($user_profile['phone']); ?></strong>
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?php echo BASE_URL; ?>lost_items/add.php" class="btn btn-danger btn-sm px-3 shadow">
                            <i class="bi bi-plus-circle me-1"></i>Report Lost Item
                        </a>
                        <a href="<?php echo BASE_URL; ?>found_items/add.php" class="btn btn-success btn-sm px-3 shadow">
                            <i class="bi bi-plus-circle me-1"></i>Report Found Item
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Dashboard Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card stat-card lost h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase text-muted small mb-1 fw-bold">My Lost Reports</h6>
                    <h2 class="mb-0 fw-bold"><?php echo $my_lost_count; ?></h2>
                    <a href="<?php echo BASE_URL; ?>lost_items/view.php" class="small text-danger text-decoration-none mt-2 d-inline-block">View list <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="stat-icon text-danger"><i class="bi bi-question-circle-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card found h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase text-muted small mb-1 fw-bold">My Found Reports</h6>
                    <h2 class="mb-0 fw-bold"><?php echo $my_found_count; ?></h2>
                    <a href="<?php echo BASE_URL; ?>found_items/view.php" class="small text-success text-decoration-none mt-2 d-inline-block">View list <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="stat-icon text-success"><i class="bi bi-check-circle-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card claims h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase text-muted small mb-1 fw-bold">My Claims Submitted</h6>
                    <h2 class="mb-0 fw-bold"><?php echo $my_claims_count; ?></h2>
                    <a href="<?php echo BASE_URL; ?>my_claims.php" class="small text-warning text-decoration-none mt-2 d-inline-block">View claims <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="stat-icon text-warning"><i class="bi bi-file-earmark-check-fill"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Main Area Split: Lists of user reports -->
<div class="row g-4">
    <!-- Lost items list -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-danger"><i class="bi bi-search me-2"></i>My Recent Lost Reports</h5>
                <a href="<?php echo BASE_URL; ?>lost_items/view.php" class="btn btn-outline-danger btn-xs px-2 py-1 small" style="font-size:0.75rem;">All Reports</a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_lost)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-info-circle display-6 mb-2 d-block"></i>
                        No lost items reported yet.
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_lost as $item): ?>
                            <div class="list-group-item px-0 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0; overflow: hidden;">
                                        <?php if ($item['image']): ?>
                                            <img src="<?php echo BASE_URL . 'uploads/' . sanitize_output($item['image']); ?>" style="width:100%; height:100%; object-fit:cover;">
                                        <?php else: ?>
                                            <i class="bi bi-image text-muted"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <h6 class="mb-0 fw-bold text-truncate"><?php echo sanitize_output($item['item_name']); ?></h6>
                                        <span class="text-secondary small font-monospace d-block" style="font-size:0.75rem;"><?php echo get_lost_uid($item['id']); ?></span>
                                        <span class="small text-muted"><i class="bi bi-geo-alt me-1"></i><?php echo sanitize_output($item['location']); ?></span>
                                    </div>
                                    <div>
                                        <span class="badge <?php 
                                            echo $item['status'] == 'Lost' ? 'bg-danger' : ($item['status'] == 'Found' ? 'bg-success' : 'bg-secondary');
                                        ?>"><?php echo sanitize_output($item['status']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Found items list -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-success"><i class="bi bi-gift me-2"></i>My Recent Found Reports</h5>
                <a href="<?php echo BASE_URL; ?>found_items/view.php" class="btn btn-outline-success btn-xs px-2 py-1 small" style="font-size:0.75rem;">All Reports</a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_found)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-info-circle display-6 mb-2 d-block"></i>
                        No found items reported yet.
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_found as $item): ?>
                            <div class="list-group-item px-0 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0; overflow: hidden;">
                                        <?php if ($item['image']): ?>
                                            <img src="<?php echo BASE_URL . 'uploads/' . sanitize_output($item['image']); ?>" style="width:100%; height:100%; object-fit:cover;">
                                        <?php else: ?>
                                            <i class="bi bi-image text-muted"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <h6 class="mb-0 fw-bold text-truncate"><?php echo sanitize_output($item['item_name']); ?></h6>
                                        <span class="text-secondary small font-monospace d-block" style="font-size:0.75rem;"><?php echo get_found_uid($item['id']); ?></span>
                                        <span class="small text-muted"><i class="bi bi-geo-alt me-1"></i><?php echo sanitize_output($item['location']); ?></span>
                                    </div>
                                    <div>
                                        <span class="badge <?php 
                                            echo $item['status'] == 'Found' ? 'bg-success' : 'bg-secondary';
                                        ?>"><?php echo sanitize_output($item['status']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
