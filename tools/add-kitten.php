<?php
session_start();
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/functions.php');

if (!isset($_SESSION['admin_id'])) {
    header('Location: /login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $dob = $_POST['dob'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $bio = trim($_POST['bio'] ?? '');
    $available = isset($_POST['available']) ? 1 : 0;
    $imagePath = null;

    if ($name === '' || !$gender) {
        $error = 'Name and gender are required.';
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Image upload failed.';
    } else {
        $uploadDir = __DIR__ . '/../images/kittens/';
        $filename = uniqid('kitten_', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $targetPath = $uploadDir . $filename;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $error = 'Failed to save image.';
        } else {
            $imagePath = '/images/kittens/' . $filename;

            $stmt = $pdo->prepare("INSERT INTO kittens (name, dob, gender, bio, image_path, available) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $dob, $gender, $bio ?: null, $imagePath, $available]);

            logAudit($_SESSION['admin_id'], "Added kitten: $name");
            $success = 'Kitten added successfully!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Kitten</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <?php include '../includes/admin-header.php'; ?>
    <main>
        <h1>Add New Kitten</h1>

        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php elseif (!empty($success)): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label for="name">Kitten Name:</label>
            <input type="text" name="name" id="name" required>

            <label for="dob">Date of Birth:</label>
            <input type="date" name="dob" id="dob">

            <label for="gender">Gender:</label>
            <select name="gender" id="gender" required>
                <option value="">Select</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>

            <label for="bio">Bio:</label>
            <textarea name="bio" id="bio" rows="5"></textarea>

            <label for="image">Kitten Image:</label>
            <input type="file" name="image" id="image" accept="image/*" required>

            <label>
                <input type="checkbox" name="available" checked>
                Available for reservation
            </label>

            <button type="submit">Add Kitten</button>
        </form>
    </main>
</body>
</html>
