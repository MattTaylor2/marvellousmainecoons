<?php
session_start();
require_once(__DIR__ . '/../includes/audit.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit;
}

$upload_dir = __DIR__ . '/../uploads/';
$message = '';

function get_mime_type($tmp_name) {
    if (!empty($tmp_name)) {
        if (function_exists('mime_content_type')) {
            return mime_content_type($tmp_name);
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);
            return $type;
        }
    }
    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['kitten_image'])) {
    $file = $_FILES['kitten_image'];

    $allowed_mime = ['image/jpeg', 'image/png'];
    $allowed_ext = ['jpg', 'jpeg', 'png'];

    $file_type = get_mime_type($file['tmp_name']);
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_type, $allowed_mime)) {
        $message = '❌ Invalid file type. Only JPEG and PNG are allowed.';
    } elseif (!in_array($file_ext, $allowed_ext)) {
        $message = '❌ Invalid file extension. Must be .jpg, .jpeg, or .png.';
    } elseif ($file['size'] > 5 * 1024 * 1024) {
        $message = '❌ File size exceeds 5MB limit.';
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $message = '❌ Upload error code: ' . $file['error'];
    } else {
        $safe_name = 'kitten_' . time() . '.' . $file_ext;
        $target = $upload_dir . $safe_name;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $message = '✅ Image uploaded successfully as ' . htmlspecialchars($safe_name);
            logAudit("Image uploaded: $safe_name", $_SESSION['user_id']);
        } else {
            $message = '❌ Failed to move uploaded file.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Kitten Images</title>
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background: #fdfdfd; }
        h1 { color: #444; }
        .message { margin-top: 1rem; color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>🖼️ Upload Kitten Images</h1>

    <?php if ($message): ?>
        <p class="<?= strpos($message, '✅') === 0 ? 'message' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label>Select image (JPG, JPEG, PNG — max 5MB):</label><br><br>
        <input type="file" name="kitten_image" accept=".jpg,.jpeg,.png" required><br><br>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
