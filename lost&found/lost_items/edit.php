<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

// Route guard: check login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to edit reports.";
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch current item details
try {
    $stmt = $pdo->prepare("SELECT * FROM lost_items WHERE id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();

    if (!$item) {
        $_SESSION['error'] = "Item not found.";
        header("Location: " . BASE_URL . "lost_items/view.php");
        exit();
    }

    // Security Check: verify owner of the record
    if ($item['user_id'] != $user_id) {
        $_SESSION['error'] = "Unauthorized access. You can only edit your own reports.";
        header("Location: " . BASE_URL . "lost_items/view.php");
        exit();
    }

} catch (PDOException $e) {
    $_SESSION['error'] = "Database query failed: " . $e->getMessage();
    header("Location: " . BASE_URL . "lost_items/view.php");
    exit();
}

$errors = [];
$item_name = $item['item_name'];
$category = $item['category'];
$description = $item['description'];
$lost_date = $item['lost_date'];
$location = $item['location'];
$status = $item['status'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_name = trim($_POST['item_name']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $lost_date = trim($_POST['lost_date']);
    $location = trim($_POST['location']);
    $status = trim($_POST['status']);

    // Validations
    if (empty($item_name)) $errors[] = "Item name is required.";
    if (empty($category)) $errors[] = "Category is required.";
    if (empty($description)) $errors[] = "Description is required.";
    if (empty($lost_date)) $errors[] = "Lost date is required.";
    if (empty($location)) $errors[] = "Lost location is required.";
    if (!in_array($status, ['Lost', 'Found', 'Claimed'])) $errors[] = "Invalid status.";

    $image_name = $item['image']; // Default to old image

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_orig_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_type = $_FILES['image']['type'];

        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 3 * 1024 * 1024; // 3MB

        // Verify file type using MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_types)) {
            $errors[] = "Invalid image file format.";
        }
        if ($file_size > $max_size) {
            $errors[] = "Image size exceeds 3MB.";
        }

        if (empty($errors)) {
            $ext = pathinfo($file_orig_name, PATHINFO_EXTENSION);
            $new_image_name = 'LOST_' . uniqid() . '_' . time() . '.' . $ext;
            $upload_dir = __DIR__ . '/../uploads/';

            if (move_uploaded_file($file_tmp, $upload_dir . $new_image_name)) {
                // Delete old image if it exists
                if (!empty($item['image']) && file_exists($upload_dir . $item['image'])) {
                    unlink($upload_dir . $item['image']);
                }
                $image_name = $new_image_name;
            } else {
                $errors[] = "Error saving uploaded image.";
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE lost_items SET item_name = ?, category = ?, description = ?, lost_date = ?, location = ?, image = ?, status = ? WHERE id = ?");
            $stmt->execute([$item_name, $category, $description, $lost_date, $location, $image_name, $status, $item_id]);

            $_SESSION['success'] = "Lost item report updated successfully!";
            header("Location: " . BASE_URL . "lost_items/view.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database update failed: " . $e->getMessage();
        }
    }
}

$page_title = "Edit Lost Report";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-danger text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Lost Report - <?php echo get_lost_uid($item_id); ?></h5>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm py-2">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo sanitize_output($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="item_name" class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="item_name" name="item_name" value="<?php echo sanitize_output($item_name); ?>" required>
                            <div class="invalid-feedback">Please enter item name.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="Electronics" <?php echo $category == 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                                <option value="Documents" <?php echo $category == 'Documents' ? 'selected' : ''; ?>>Documents</option>
                                <option value="Personal Belongings" <?php echo $category == 'Personal Belongings' ? 'selected' : ''; ?>>Personal Belongings</option>
                                <option value="Clothing/Accessories" <?php echo $category == 'Clothing/Accessories' ? 'selected' : ''; ?>>Clothing/Accessories</option>
                                <option value="Others" <?php echo $category == 'Others' ? 'selected' : ''; ?>>Others</option>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="lost_date" class="form-label fw-semibold">Date Lost <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="lost_date" name="lost_date" max="<?php echo date('Y-m-d'); ?>" value="<?php echo sanitize_output($lost_date); ?>" required>
                            <div class="invalid-feedback">Please enter a valid date.</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="location" class="form-label fw-semibold">Lost Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo sanitize_output($location); ?>" required>
                            <div class="invalid-feedback">Please enter lost location.</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Lost" <?php echo $status == 'Lost' ? 'selected' : ''; ?>>Lost</option>
                                <option value="Found" <?php echo $status == 'Found' ? 'selected' : ''; ?>>Found (Recovered by self)</option>
                                <option value="Claimed" <?php echo $status == 'Claimed' ? 'selected' : ''; ?>>Claimed (Returned)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Detailed Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="4" required><?php echo sanitize_output($description); ?></textarea>
                        <div class="invalid-feedback">Please provide detailed description.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold d-block">Current Image</label>
                        <?php if ($item['image']): ?>
                            <div class="mb-2">
                                <img src="<?php echo BASE_URL . 'uploads/' . sanitize_output($item['image']); ?>" class="img-thumbnail" style="max-height: 150px;">
                            </div>
                        <?php else: ?>
                            <p class="text-muted small">No image uploaded.</p>
                        <?php endif; ?>
                        
                        <label for="image" class="form-label fw-semibold">Upload New Image <span class="text-muted">(Optional, will replace old image)</span></label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="form-text">Max size: 3MB. Formats: JPG, PNG, GIF, WEBP.</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?php echo BASE_URL; ?>lost_items/view.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-save-fill me-1"></i>Update Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
