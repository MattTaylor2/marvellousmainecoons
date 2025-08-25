<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit;
}

$upload_dir = __DIR__ . '/../uploads/';
$images = [];

foreach (scandir($upload_dir) as $file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        $images[] = $file;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Uploaded Kitten Images</title>
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background: #fdfdfd; }
        h1 { color: #444; }
        .grid { display: flex; flex-wrap: wrap; gap: 1rem; }
        .card { border: 1px solid #ccc; padding: 0.5rem; background: #fff; width: 200px; }
        .card img { max-width: 100%; height: auto; display: block; }
        .filename { font-size: 0.9rem; color: #666; margin-top: 0.5rem; word-break: break-word; }
    </style>
</head>
<body>
    <h1>📸 Uploaded Kitten Images</h1>

    <?php if (empty($images)): ?>
        <p>No images found in uploads directory.</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($images as $img): ?>
                <div class="card">
                    <img src="/uploads/<?= urlencode($img) ?>" alt="Kitten image">
                    <div class="filename"><?= htmlspecialchars($img) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>
