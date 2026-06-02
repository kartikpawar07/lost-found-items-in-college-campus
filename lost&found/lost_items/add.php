<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

// Route guard: student must be logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to report a lost item.";
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$errors = [];
$item_name = $category = $description = $lost_date = $location = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_name = trim($_POST['item_name']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $lost_date = trim($_POST['lost_date']);
    $location = trim($_POST['location']);

    // Validations
    if (empty($item_name)) $errors[] = "Item name is required.";
    if (empty($category)) $errors[] = "Category is required.";
    if (empty($description)) $errors[] = "Description is required.";
    if (empty($lost_date)) $errors[] = "Lost date is required.";
    if (empty($location)) $errors[] = "Lost location is required.";

    // Handle Image Upload
    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_orig_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_type = $_FILES['image']['type'];

        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 3 * 1024 * 1024; // 3MB

        // Verify type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_types)) {
            $errors[] = "Invalid image file format. Only JPG, PNG, GIF, and WEBP are allowed.";
        }
        if ($file_size > $max_size) {
            $errors[] = "Image size exceeds the maximum limit of 3MB.";
        }

        if (empty($errors)) {
            $ext = pathinfo($file_orig_name, PATHINFO_EXTENSION);
            $image_name = 'LOST_' . uniqid() . '_' . time() . '.' . $ext;
            
            // Check and create uploads folder if not existing
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (!move_uploaded_file($file_tmp, $upload_dir . $image_name)) {
                $errors[] = "Error saving uploaded image.";
                $image_name = null;
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO lost_items (user_id, item_name, category, description, lost_date, location, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Lost')");
            $stmt->execute([$_SESSION['user_id'], $item_name, $category, $description, $lost_date, $location, $image_name]);

            $_SESSION['success'] = "Lost item reported successfully!";
            header("Location: " . BASE_URL . "lost_items/view.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database insert failed: " . $e->getMessage();
        }
    }
}

$page_title = "Report Lost Item";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-danger text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-megaphone-fill me-2"></i>Report Lost Item</h5>
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

                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="item_name" class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="item_name" name="item_name" value="<?php echo sanitize_output($item_name); ?>" placeholder="e.g. Blue Backpack, HP Laptop" required>
                            <div class="invalid-feedback">Please enter the item name.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="" disabled selected>Select Category</option>
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
                        <div class="col-md-6 mb-3">
                            <label for="lost_date" class="form-label fw-semibold">Date Lost <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="lost_date" name="lost_date" max="<?php echo date('Y-m-d'); ?>" value="<?php echo sanitize_output($lost_date); ?>" required>
                            <div class="invalid-feedback">Please enter a valid date.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label fw-semibold">Lost Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo sanitize_output($location); ?>" placeholder="e.g. Main Library, Room 204" required>
                            <div class="invalid-feedback">Please enter where the item was lost.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Detailed Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Provide distinct characteristics like color, brand, serial number, stickers..." required><?php echo sanitize_output($description); ?></textarea>
                        <div class="invalid-feedback">Please provide a detailed description of the item.</div>
                    </div>

                    <div class="mb-4">
                        <label for="image" class="form-label fw-semibold">Item Image <span class="text-muted">(Optional)</span></label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="form-text">Max size: 3MB. Formats: JPG, PNG, GIF, WEBP.</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-send-fill me-1"></i>Submit Report
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
