<?php
$page_title = "Admin Dashboard";
require_once __DIR__ . '/../includes/admin_header.php';

// Fetch overall campus statistics
try {
    // 1. Total Registered Students
    $students_stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_students = $students_stmt->fetchColumn();

    // 2. Lost items stats
    $lost_stmt = $pdo->query("SELECT COUNT(*) FROM lost_items");
    $total_lost = $lost_stmt->fetchColumn();

    $lost_active_stmt = $pdo->query("SELECT COUNT(*) FROM lost_items WHERE status = 'Lost'");
    $lost_active = $lost_active_stmt->fetchColumn();

    // 3. Found items stats
    $found_stmt = $pdo->query("SELECT COUNT(*) FROM found_items");
    $total_found = $found_stmt->fetchColumn();

    $found_active_stmt = $pdo->query("SELECT COUNT(*) FROM found_items WHERE status = 'Found'");
    $found_active = $found_active_stmt->fetchColumn();

    // 4. Claims stats
    $claims_stmt = $pdo->query("SELECT COUNT(*) FROM claims");
    $total_claims = $claims_stmt->fetchColumn();

    $claims_pending_stmt = $pdo->query("SELECT COUNT(*) FROM claims WHERE status = 'Pending'");
    $claims_pending = $claims_pending_stmt->fetchColumn();

    $claims_approved_stmt = $pdo->query("SELECT COUNT(*) FROM claims WHERE status = 'Approved'");
    $claims_approved = $claims_approved_stmt->fetchColumn();

    // 5. Category breakdown for report
    $cat_stmt = $pdo->query("
        SELECT category, COUNT(*) as cnt, 'Lost' as type FROM lost_items GROUP BY category
        UNION ALL
        SELECT category, COUNT(*) as cnt, 'Found' as type FROM found_items GROUP BY category
    ");
    $category_stats = $cat_stmt->fetchAll();

} catch (PDOException $e) {
    $total_students = $total_lost = $lost_active = $total_found = $found_active = $total_claims = $claims_pending = $claims_approved = 0;
    $category_stats = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark mb-1"><i class="bi bi-speedometer2 me-2 text-primary"></i>Admin Overview</h2>
        <p class="text-muted mb-0">System health, users registry, and claim logs statistics.</p>
    </div>
    <button class="btn btn-primary btn-sm shadow" data-bs-toggle="modal" data-bs-target="#reportModal">
        <i class="bi bi-file-earmark-bar-graph me-1"></i>Generate Summary Report
    </button>
</div>

<!-- Stats Indicators Row -->
<div class="row g-4 mb-5">
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card lost h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase text-muted small mb-1 fw-bold">Registered Students</h6>
                    <h2 class="mb-0 fw-bold"><?php echo $total_students; ?></h2>
                    <a href="users.php" class="small text-danger text-decoration-none mt-2 d-inline-block">Manage Users <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="stat-icon text-danger"><i class="bi bi-people-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card found h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase text-muted small mb-1 fw-bold">Active Lost Reports</h6>
                    <h2 class="mb-0 fw-bold"><?php echo $lost_active; ?> <span class="text-muted small fs-6">/ <?php echo $total_lost; ?> total</span></h2>
                    <a href="lost_items.php" class="small text-success text-decoration-none mt-2 d-inline-block">View Lost <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="stat-icon text-success"><i class="bi bi-search"></i></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card claims h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase text-muted small mb-1 fw-bold">Unclaimed Found</h6>
                    <h2 class="mb-0 fw-bold"><?php echo $found_active; ?> <span class="text-muted small fs-6">/ <?php echo $total_found; ?> total</span></h2>
                    <a href="found_items.php" class="small text-warning text-decoration-none mt-2 d-inline-block">View Found <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="stat-icon text-warning"><i class="bi bi-gift-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card stat-card h-100" style="border-left-color: #6c5ce7;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase text-muted small mb-1 fw-bold">Pending Claims</h6>
                    <h2 class="mb-0 fw-bold text-dark"><?php echo $claims_pending; ?> <span class="text-muted small fs-6">/ <?php echo $total_claims; ?> total</span></h2>
                    <a href="claims.php" class="small text-decoration-none mt-2 d-inline-block" style="color: #6c5ce7;">Review Claims <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="stat-icon" style="color: #6c5ce7; opacity: 0.3;"><i class="bi bi-file-earmark-check-fill"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Administration Quick Actions Panels -->
<div class="row g-4">
    <div class="col-md-6 col-lg-3">
        <div class="card text-center p-3 h-100">
            <div class="card-body">
                <i class="bi bi-people display-5 text-primary mb-3"></i>
                <h5 class="fw-bold">Manage Students</h5>
                <p class="text-muted small">View all registered students, delete fake student profiles.</p>
                <a href="users.php" class="btn btn-outline-primary btn-sm w-100 mt-2">Go to Students</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card text-center p-3 h-100">
            <div class="card-body">
                <i class="bi bi-search display-5 text-danger mb-3"></i>
                <h5 class="fw-bold">Lost Registry</h5>
                <p class="text-muted small">Monitor reports of lost items and delete duplicate entries.</p>
                <a href="lost_items.php" class="btn btn-outline-danger btn-sm w-100 mt-2">Go to Lost Items</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card text-center p-3 h-100">
            <div class="card-body">
                <i class="bi bi-gift display-5 text-success mb-3"></i>
                <h5 class="fw-bold">Found Registry</h5>
                <p class="text-muted small">Monitor reports of found items and manage details.</p>
                <a href="found_items.php" class="btn btn-outline-success btn-sm w-100 mt-2">Go to Found Items</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card text-center p-3 h-100">
            <div class="card-body">
                <i class="bi bi-file-earmark-check display-5 text-warning mb-3"></i>
                <h5 class="fw-bold">Review Claims</h5>
                <p class="text-muted small">Verify proof, and Approve or Reject student claim requests.</p>
                <a href="claims.php" class="btn btn-outline-warning btn-sm w-100 mt-2 text-dark">Go to Claims</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Report Details -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div id="printableReport" class="p-4 bg-white">
                <div class="modal-header border-0 pb-0 d-flex justify-content-between">
                    <div>
                        <h4 class="fw-bold text-primary mb-1" id="reportModalLabel">
                            <i class="bi bi-journal-check me-2"></i>Smart Campus Lost and Found
                        </h4>
                        <h6 class="text-uppercase text-muted small fw-bold">System Summary Report</h6>
                    </div>
                    <div class="text-end text-muted small">
                        <div>Report Date: <?php echo date('d M Y'); ?></div>
                        <div>Role: Administrator</div>
                    </div>
                </div>
                <hr class="my-3">
                <div class="modal-body">
                    <!-- General statistics block -->
                    <h5 class="fw-bold mb-3 text-secondary">General Registry Metrics</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 text-center">
                                <span class="small text-muted d-block text-uppercase">Total Users</span>
                                <h3 class="fw-bold mb-0 text-dark"><?php echo $total_students; ?></h3>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 text-center">
                                <span class="small text-muted d-block text-uppercase">Lost Items</span>
                                <h3 class="fw-bold mb-0 text-danger"><?php echo $total_lost; ?></h3>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 text-center">
                                <span class="small text-muted d-block text-uppercase">Found Items</span>
                                <h3 class="fw-bold mb-0 text-success"><?php echo $total_found; ?></h3>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 text-center">
                                <span class="small text-muted d-block text-uppercase">Claim Logs</span>
                                <h3 class="fw-bold mb-0 text-warning"><?php echo $total_claims; ?></h3>
                            </div>
                        </div>
                    </div>

                    <!-- Claims Stats table -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="fw-bold mb-3 text-secondary">Claim Requests Status</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>Pending Review</span>
                                    <span class="badge bg-warning text-dark rounded-pill fw-bold"><?php echo $claims_pending; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>Approved claims</span>
                                    <span class="badge bg-success rounded-pill fw-bold"><?php echo $claims_approved; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>Rejected claims</span>
                                    <span class="badge bg-danger rounded-pill fw-bold"><?php echo ($total_claims - $claims_pending - $claims_approved); ?></span>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Category breakdown Table -->
                        <div class="col-md-6">
                            <h5 class="fw-bold mb-3 text-secondary">Category Registry Overview</h5>
                            <div style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-sm table-bordered mb-0 text-center small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Category</th>
                                            <th>Type</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($category_stats)): ?>
                                            <tr>
                                                <td colspan="3" class="text-muted">No entries</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($category_stats as $stat): ?>
                                                <tr>
                                                    <td><?php echo sanitize_output($stat['category']); ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $stat['type'] == 'Lost' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'; ?> small">
                                                            <?php echo $stat['type']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="fw-bold"><?php echo $stat['cnt']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3 border-top pt-3 small text-muted">
                        Smart Campus Lost and Found Management System Report generated on local server.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="window.print();">
                    <i class="bi bi-printer-fill me-1"></i>Print Report
                </button>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/admin_footer.php';
?>
