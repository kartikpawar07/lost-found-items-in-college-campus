<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to perform this action.";
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($item_id <= 0) {
    $_SESSION['error'] = "Invalid item selection.";
    header("Location: " . BASE_URL . "lost_items/view.php");
    exit();
}

try {
    // Fetch item details to verify owner and check image filename
    $stmt = $pdo->prepare("SELECT * FROM lost_items WHERE id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();

    if (!$item) {
        $_SESSION['error'] = "Item not found.";
    } elseif ($item['user_id'] != $user_id) {
        $_SESSION['error'] = "Unauthorized access! You can only delete your own reports.";
    } else {
        // Delete image file if it exists
        if (!empty($item['image'])) {
            $image_path = __DIR__ . '/../uploads/' . $item['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // Delete DB record
        $del_stmt = $pdo->prepare("DELETE FROM lost_items WHERE id = ?");
        $del_stmt->execute([$item_id]);

        $_SESSION['success'] = "Lost item report deleted successfully!";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Failed to delete record: " . $e->getMessage();
}

header("Location: " . BASE_URL . "lost_items/view.php");
exit();
?>
