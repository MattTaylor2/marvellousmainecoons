<?php
require_once '../includes/functions.php';
require_once '../includes/csrf.php';
requireAdmin();

$db = getDb();
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    http_response_code(400);
    echo "<h1>400 Bad Request</h1><p>Invalid kitten ID.</p>";
    exit;
}

// Fetch kitten to confirm existence and image path
$stmt = $db->prepare("SELECT * FROM kittens WHERE id = ?");
$stmt->execute([$id]);
$kitten = $stmt->fetch();

if (!$kitten) {
    http_response_code(404);
    echo "<h1>404 Not Found</h1><p>Kitten not found.</p>";
    exit;
}

// If POST, verify CSRF and delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    // Delete image file if exists
    if ($kitten['image_path']) {
        $imageFile = __DIR__ . '/../images/uploads/' . $kitten['image_path'];
        if (file_exists($imageFile)) {
            unlink($imageFile);
        }
    }

    // Delete from DB
    $stmt = $db->prepare("DELETE FROM kittens WHERE id = ?");
    $stmt->execute([$id]);

    // Optional: logAction('delete_kitten', $_SESSION['admin_id'], $id);

    header("Location: manage_kittens.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Kitten</title>
    <link rel="stylesheet" href="/css/styles.css">
    <style>
        main {
            max-width: 600px;
            margin: 60px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        img {
            max-width: 200px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        button {
            padding: 10px 20px;
            background: red;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        a.cancel {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #555;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <h2>Delete Kitten: <?= htmlspecialchars($kitten['name']) ?></h2>
        <?php if ($kitten['image_path']): ?>
            <img src="/images/uploads/<?= htmlspecialchars($kitten['image_path']) ?>" alt="Kitten Image">
        <?php endif; ?>
        <p>Are you sure you want to permanently delete this kitten profile?</p>

        <form method="POST">
            <?php csrfInput(); ?>
            <button type="submit">Yes, Delete</button>
        </form>

        <a href="manage_kittens.php" class="cancel">Cancel</a>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
