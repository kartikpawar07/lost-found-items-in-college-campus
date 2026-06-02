<?php
$page_title = "Search Items";
require_once __DIR__ . '/includes/header.php';

// Form inputs
$q_name = isset($_GET['name']) ? trim($_GET['name']) : '';
$q_category = isset($_GET['category']) ? trim($_GET['category']) : '';
$q_location = isset($_GET['location']) ? trim($_GET['location']) : '';
$q_type = isset($_GET['type']) ? trim($_GET['type']) : 'all'; // all, lost, found

$results = [];
$params = [];
$contact_modals = [];

// Base unified query inside a subquery for easy filtering
$sql = "SELECT * FROM (
    SELECT id, user_id, item_name, category, description, lost_date AS item_date, location, image, status, 'Lost' AS item_type 
    FROM lost_items 
    UNION ALL 
    SELECT id, user_id, item_name, category, description, found_date AS item_date, location, image, status, 'Found' AS item_type 
    FROM found_items
) AS campus_items WHERE 1=1";

if (!empty($q_name)) {
    $sql .= " AND item_name LIKE :name";
    $params['name'] = '%' . $q_name . '%';
}

if (!empty($q_category)) {
    $sql .= " AND category = :category";
    $params['category'] = $q_category;
}

if (!empty($q_location)) {
    $sql .= " AND location LIKE :location";
    $params['location'] = '%' . $q_location . '%';
}

if ($q_type === 'lost') {
    $sql .= " AND item_type = 'Lost'";
} elseif ($q_type === 'found') {
    $sql .= " AND item_type = 'Found'";
}

$sql .= " ORDER BY item_date DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Search failed: " . $e->getMessage();
}
?>

<div class="row">
    <!-- Search Form -->
    <div class="col-12 mb-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-search me-2"></i>Search Campus Lost & Found Registry</h5>
            </div>
            <div class="card-body p-4">
                <form action="search.php" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="name" class="form-label fw-semibold">Item Name / Keyword</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo sanitize_output($q_name); ?>" placeholder="e.g. iPhone, Wallet">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="form-label fw-semibold">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            <option value="Electronics" <?php echo $q_category == 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                            <option value="Documents" <?php echo $q_category == 'Documents' ? 'selected' : ''; ?>>Documents</option>
                            <option value="Personal Belongings" <?php echo $q_category == 'Personal Belongings' ? 'selected' : ''; ?>>Personal Belongings</option>
                            <option value="Clothing/Accessories" <?php echo $q_category == 'Clothing/Accessories' ? 'selected' : ''; ?>>Clothing/Accessories</option>
                            <option value="Others" <?php echo $q_category == 'Others' ? 'selected' : ''; ?>>Others</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="location" class="form-label fw-semibold">Location</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-geo-alt text-muted"></i></span>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo sanitize_output($q_location); ?>" placeholder="e.g. Library, Canteen">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="type" class="form-label fw-semibold">Registry Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="all" <?php echo $q_type == 'all' ? 'selected' : ''; ?>>All Items</option>
                            <option value="lost" <?php echo $q_type == 'lost' ? 'selected' : ''; ?>>Lost Items</option>
                            <option value="found" <?php echo $q_type == 'found' ? 'selected' : ''; ?>>Found Items</option>
                        </select>
                    </div>
                    <div class="col-12 text-end mt-4">
                        <a href="search.php" class="btn btn-outline-secondary me-2">Clear Filters</a>
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-filter me-1"></i>Search Registry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <div class="col-12">
        <h4 class="fw-bold mb-4 text-primary border-bottom pb-2">
            <i class="bi bi-list-stars me-2"></i>Search Results (<?php echo count($results); ?>)
        </h4>

        <?php if (empty($results)): ?>
            <div class="card text-center py-5 border-0 shadow-sm">
                <div class="card-body">
                    <i class="bi bi-search display-1 text-muted mb-3 d-block"></i>
                    <h5 class="fw-bold text-dark">No records found</h5>
                    <p class="text-muted">We couldn't find any items matching your filters. Try refining your keyword or searching across all categories.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($results as $item): ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="card h-100">
                            <div class="item-img-container">
                                <?php if ($item['item_type'] == 'Lost'): ?>
                                    <span class="badge bg-danger badge-status"><?php echo sanitize_output($item['status']); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success badge-status"><?php echo sanitize_output($item['status']); ?></span>
                                <?php endif; ?>

                                <?php if ($item['image']): ?>
                                    <img src="<?php echo BASE_URL . 'uploads/' . sanitize_output($item['image']); ?>" alt="<?php echo sanitize_output($item['item_name']); ?>">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center h-100 text-muted bg-light">
                                        <i class="bi bi-image display-4"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div>
                                        <h5 class="card-title fw-bold mb-0"><?php echo sanitize_output($item['item_name']); ?></h5>
                                        <span class="text-secondary font-monospace small" style="font-size: 0.8rem; display: block; margin-top: 2px;">
                                            ID: <?php echo $item['item_type'] == 'Lost' ? get_lost_uid($item['id']) : get_found_uid($item['id']); ?>
                                        </span>
                                    </div>
                                    <span class="badge bg-light text-dark border small ms-1"><?php echo sanitize_output($item['item_type']); ?></span>
                                </div>
                                <span class="badge bg-secondary mb-2 align-self-start"><?php echo sanitize_output($item['category']); ?></span>
                                <p class="card-text text-muted flex-grow-1 text-truncate-3"><?php echo sanitize_output($item['description']); ?></p>
                                <hr class="my-2">
                                <div class="small text-muted mb-3">
                                    <div><i class="bi bi-geo-alt-fill me-1"></i>Location: <strong><?php echo sanitize_output($item['location']); ?></strong></div>
                                    <div><i class="bi bi-calendar-event me-1"></i>Date: <?php echo date('M d, Y', strtotime($item['item_date'])); ?></div>
                                </div>
                                
                                <div class="mt-auto">
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <?php if ($item['item_type'] == 'Found'): ?>
                                            <?php if ($item['user_id'] == $_SESSION['user_id']): ?>
                                                <button class="btn btn-secondary btn-sm w-100 disabled">Your Found Item</button>
                                            <?php elseif ($item['status'] == 'Claimed'): ?>
                                                <button class="btn btn-secondary btn-sm w-100 disabled">Already Claimed</button>
                                            <?php else: ?>
                                                <a href="<?php echo BASE_URL; ?>claim_form.php?found_id=<?php echo $item['id']; ?>" class="btn btn-success btn-sm w-100">
                                                    <i class="bi bi-file-earmark-check me-1"></i>Claim Item
                                                </a>
                                            <?php endif; ?>
                                        <?php else: // Lost item ?>
                                            <?php if ($item['user_id'] == $_SESSION['user_id']): ?>
                                                <a href="<?php echo BASE_URL; ?>lost_items/edit.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-danger btn-sm w-100">
                                                    <i class="bi bi-pencil-square me-1"></i>Edit Lost Report
                                                </a>
                                            <?php else: ?>
                                                <!-- Simple contact popover details -->
                                                <?php
                                                    // Fetch owner details
                                                    try {
                                                        $owner_stmt = $pdo->prepare("SELECT name, email, phone, department FROM users WHERE id = ?");
                                                        $owner_stmt->execute([$item['user_id']]);
                                                        $owner = $owner_stmt->fetch();
                                                    } catch (PDOException $ex) {
                                                        $owner = null;
                                                    }
                                                ?>
                                                <?php if ($owner): ?>
                                                    <button type="button" class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#contactModal<?php echo $item['id']; ?>">
                                                        <i class="bi bi-telephone-fill me-1"></i>Contact Owner
                                                    </button>
                                                    <?php
                                                        // Save modal data to render outside the card loop to prevent positioning bugs due to parent transform animations
                                                        $contact_modals[] = [
                                                            'item_id' => $item['id'],
                                                            'owner' => $owner
                                                        ];
                                                    ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-outline-primary btn-sm w-100">Login to Contact/Claim</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($contact_modals)): ?>
    <?php foreach ($contact_modals as $modal_data): 
        $modal_item_id = $modal_data['item_id'];
        $modal_owner = $modal_data['owner'];
    ?>
        <!-- Contact Modal for Item ID <?php echo $modal_item_id; ?> -->
        <div class="modal fade" id="contactModal<?php echo $modal_item_id; ?>" tabindex="-1" aria-labelledby="contactModalLabel<?php echo $modal_item_id; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="contactModalLabel<?php echo $modal_item_id; ?>">Owner Contact Information</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <i class="bi bi-person-circle display-4 text-primary"></i>
                            <h5 class="fw-bold mt-2"><?php echo sanitize_output($modal_owner['name']); ?></h5>
                            <span class="badge bg-secondary"><?php echo sanitize_output($modal_owner['department']); ?></span>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="bi bi-envelope-fill me-2 text-primary"></i>Email:</span>
                                <strong><a href="mailto:<?php echo $modal_owner['email']; ?>"><?php echo sanitize_output($modal_owner['email']); ?></a></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="bi bi-telephone-fill me-2 text-primary"></i>Phone:</span>
                                <strong><a href="tel:<?php echo $modal_owner['phone']; ?>"><?php echo sanitize_output($modal_owner['phone']); ?></a></strong>
                            </li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
