<?php
session_start();
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/audit.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit;
}

$pdo = getDb();
$upload_dir = __DIR__ . '/../uploads/';
$images = [];
$message = '';

foreach (scandir($upload_dir) as $file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        $images[] = $file;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kitten_name = trim($_POST['kitten_name'] ?? '');
    $image_file = $_POST['image_file'] ?? '';

    if ($kitten_name && $image_file) {
        $stmt = $pdo->prepare("UPDATE kittens SET image_filename = :img WHERE name = :name");
        $stmt->execute([
            ':img' => $image_file,
            ':name' => $kitten_name
        ]);
        logAudit("Assigned image '$image_file' to kitten '$kitten_name'", $_SESSION['user_id']);
        $message = "✅ Assigned $image_file to $kitten_name.";
    } else {
        $message = "❌ Missing kitten name or image selection.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Images to Kittens</title>
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background: #fdfdfd; }
        h1 { color: #444; }
        .grid { display: flex; flex-wrap: wrap; gap: 1rem; }
        .card { border: 1px solid #ccc; padding: 0.5rem; background: #fff; width: 200px; }
        .card img { max-width: 100%; height: auto; display: block; }
        .filename { font-size: 0.9rem; color: #666; margin-top: 0.5rem; word-break: break-word; }
        form { margin-top: 1rem; }
    </style>
</head>
<body>
    <h1>🔗 Assign Images to Kittens</h1>

    <?php if ($message): ?>
        <p class="<?= strpos($message, '✅') === 0 ? 'message' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <?php foreach ($images as $img): ?>
        <div class="card">
            <img src="/uploads/<?= urlencode($img) ?>" alt="Kitten image">
            <div class="filename"><?= htmlspecialchars($img) ?></div>
            <form method="post">
                <input type="hidden" name="image_file" value="<?= htmlspecialchars($img) ?>">
                <input type="text" name="kitten_name" placeholder="Kitten name" required>
                <button type="submit">Assign</button>
            </form>
        </div>
    <?php endforeach; ?>
</body>
</html>
