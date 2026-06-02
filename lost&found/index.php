<?php
$page_title = "Welcome";
require_once __DIR__ . '/includes/header.php';

// Fetch statistics
try {
    // Total Lost Items (Status is 'Lost')
    $stmt = $pdo->query("SELECT COUNT(*) FROM lost_items WHERE status = 'Lost'");
    $total_lost = $stmt->fetchColumn();

    // Total Found Items (Status is 'Found')
    $stmt = $pdo->query("SELECT COUNT(*) FROM found_items WHERE status = 'Found'");
    $total_found = $stmt->fetchColumn();

    // Total Claims (All status levels)
    $stmt = $pdo->query("SELECT COUNT(*) FROM claims");
    $total_claims = $stmt->fetchColumn();

    // Fetch 3 most recent Lost Items
    $stmt = $pdo->query("SELECT li.*, u.name as user_name FROM lost_items li JOIN users u ON li.user_id = u.id WHERE li.status = 'Lost' ORDER BY li.id DESC LIMIT 3");
    $recent_lost = $stmt->fetchAll();

    // Fetch 3 most recent Found Items
    $stmt = $pdo->query("SELECT fi.*, u.name as user_name FROM found_items fi JOIN users u ON fi.user_id = u.id WHERE fi.status = 'Found' ORDER BY fi.id DESC LIMIT 3");
    $recent_found = $stmt->fetchAll();

} catch (PDOException $e) {
    $total_lost = $total_found = $total_claims = 0;
    $recent_lost = $recent_found = [];
}
?>

<!-- Hero Banner Section -->
<div class="row hero-section justify-content-center text-center mx-0 px-3">
    <div class="col-lg-8">
        <h1 class="display-4 fw-bold mb-3"><i class="bi bi-geo-alt-fill text-success"></i> Campus Lost & Found Hub</h1>
        <p class="lead mb-4">A unified portal for students and faculty of our college campus to report, search, and claim lost items easily.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?php echo BASE_URL; ?>search.php" class="btn btn-success btn-lg px-4">
                <i class="bi bi-search me-2"></i>Search Items
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-outline-light btn-lg px-4">
                    <i class="bi bi-speedometer2 me-2"></i>My Dashboard
                </a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-outline-light btn-lg px-4">
                    <i class="bi bi-person-plus-fill me-2"></i>Register Now
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Stats Counter Block -->
<div class="row justify-content-center mb-5 mt-n4">
    <div class="col-lg-10">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card stat-card lost h-100 py-2">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-muted small mb-1 fw-bold">Active Lost Items</h6>
                            <h2 class="mb-0 fw-bold text-dark"><?php echo $total_lost; ?></h2>
                        </div>
                        <div class="stat-icon text-danger"><i class="bi bi-question-circle-fill"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card found h-100 py-2">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-muted small mb-1 fw-bold">Unclaimed Found Items</h6>
                            <h2 class="mb-0 fw-bold text-dark"><?php echo $total_found; ?></h2>
                        </div>
                        <div class="stat-icon text-success"><i class="bi bi-check-circle-fill"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card claims h-100 py-2">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-muted small mb-1 fw-bold">Total Claim Requests</h6>
                            <h2 class="mb-0 fw-bold text-dark"><?php echo $total_claims; ?></h2>
                        </div>
                        <div class="stat-icon text-warning"><i class="bi bi-file-earmark-check-fill"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Listings Section -->
<div class="container my-5">
    <!-- Recent Lost Items -->
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
        <h3 class="fw-bold text-primary"><i class="bi bi-search me-2"></i>Recently Reported Lost Items</h3>
        <a href="<?php echo BASE_URL; ?>search.php" class="btn btn-outline-primary btn-sm">View All</a>
    </div>
    
    <div class="row g-4 mb-5">
        <?php if (empty($recent_lost)): ?>
            <div class="col-12 text-center text-muted my-4">
                <i class="bi bi-info-circle display-6 mb-2 d-block"></i>
                No lost items reported recently.
            </div>
        <?php else: ?>
            <?php foreach ($recent_lost as $item): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="item-img-container">
                            <span class="badge bg-danger badge-status"><?php echo sanitize_output($item['status']); ?></span>
                            <?php if ($item['image']): ?>
                                <img src="<?php echo BASE_URL . 'uploads/' . sanitize_output($item['image']); ?>" alt="<?php echo sanitize_output($item['item_name']); ?>">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center h-100 text-muted bg-light">
                                    <i class="bi bi-image display-4"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold mb-1">
                                <?php echo sanitize_output($item['item_name']); ?>
                                <span class="text-secondary font-monospace small" style="font-size: 0.8rem; display: block; margin-top: 2px;">
                                    ID: <?php echo get_lost_uid($item['id']); ?>
                                </span>
                            </h5>
                            <span class="badge bg-secondary mb-2 align-self-start"><?php echo sanitize_output($item['category']); ?></span>
                            <p class="card-text text-muted flex-grow-1 text-truncate-3"><?php echo sanitize_output($item['description']); ?></p>
                            <hr class="my-2">
                            <div class="small text-muted mb-3">
                                <div><i class="bi bi-geo-alt-fill me-1"></i>Lost at: <strong><?php echo sanitize_output($item['location']); ?></strong></div>
                                <div><i class="bi bi-calendar-event me-1"></i>Date: <?php echo date('M d, Y', strtotime($item['lost_date'])); ?></div>
                            </div>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="<?php echo BASE_URL; ?>search.php" class="btn btn-primary btn-sm mt-auto w-100">View Details</a>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-outline-primary btn-sm mt-auto w-100">Login to Contact</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Recent Found Items -->
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
        <h3 class="fw-bold text-success"><i class="bi bi-gift-fill me-2"></i>Recently Found Items</h3>
        <a href="<?php echo BASE_URL; ?>search.php" class="btn btn-outline-success btn-sm">View All</a>
    </div>

    <div class="row g-4">
        <?php if (empty($recent_found)): ?>
            <div class="col-12 text-center text-muted my-4">
                <i class="bi bi-info-circle display-6 mb-2 d-block"></i>
                No found items reported recently.
            </div>
        <?php else: ?>
            <?php foreach ($recent_found as $item): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="item-img-container">
                            <span class="badge bg-success badge-status"><?php echo sanitize_output($item['status']); ?></span>
                            <?php if ($item['image']): ?>
                                <img src="<?php echo BASE_URL . 'uploads/' . sanitize_output($item['image']); ?>" alt="<?php echo sanitize_output($item['item_name']); ?>">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center h-100 text-muted bg-light">
                                    <i class="bi bi-image display-4"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold mb-1">
                                <?php echo sanitize_output($item['item_name']); ?>
                                <span class="text-secondary font-monospace small" style="font-size: 0.8rem; display: block; margin-top: 2px;">
                                    ID: <?php echo get_found_uid($item['id']); ?>
                                </span>
                            </h5>
                            <span class="badge bg-secondary mb-2 align-self-start"><?php echo sanitize_output($item['category']); ?></span>
                            <p class="card-text text-muted flex-grow-1 text-truncate-3"><?php echo sanitize_output($item['description']); ?></p>
                            <hr class="my-2">
                            <div class="small text-muted mb-3">
                                <div><i class="bi bi-geo-alt-fill me-1"></i>Found at: <strong><?php echo sanitize_output($item['location']); ?></strong></div>
                                <div><i class="bi bi-calendar-event me-1"></i>Date: <?php echo date('M d, Y', strtotime($item['found_date'])); ?></div>
                            </div>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="d-grid gap-2">
                                    <?php if ($item['user_id'] == $_SESSION['user_id']): ?>
                                        <button class="btn btn-secondary btn-sm disabled">Your Found Item</button>
                                    <?php else: ?>
                                        <a href="<?php echo BASE_URL; ?>claim_form.php?found_id=<?php echo $item['id']; ?>" class="btn btn-success btn-sm">
                                            <i class="bi bi-file-earmark-check me-1"></i>Submit Claim
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-outline-success btn-sm mt-auto w-100">Login to Claim</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
