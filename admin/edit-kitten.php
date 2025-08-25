<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: /login.php');
    exit;
}

$kittenId = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM kittens WHERE id = ?");
$stmt->execute([$kittenId]);
$kitten = $stmt->fetch();

if (!$kitten) {
    die('Kitten not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $dob = $_POST['dob'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $bio = trim($_POST['bio'] ?? '');
    $available = isset($_POST['available']) ? 1 : 0;
    $imagePath = $kitten['image_path'];

    if ($name === '' || !$gender) {
        $error = 'Name and gender are required.';
    } else {
        // 🖼️ Handle new image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../images/kittens/';
            $filename = uniqid('kitten_', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePath = '/images/kittens/' . $filename;
            }
        }

        $stmt = $pdo->prepare("UPDATE kittens SET name = ?, dob = ?, gender = ?, bio = ?, image_path = ?, available = ? WHERE id = ?");
        $stmt->execute([$name, $dob, $gender, $bio ?: null, $imagePath, $available, $kittenId]);

        logAudit($_SESSION['admin_id'], "Edited kitten ID: $kittenId");
        $success = 'Kitten updated successfully!';

        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM kittens WHERE id = ?");
        $stmt->execute([$kittenId]);
        $kitten = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Kitten</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <?php include '../includes/admin-header.php'; ?>
    <main>
        <h1>Edit Kitten</h1>

        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php elseif (!empty($success)): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label for="name">Kitten Name:</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($kitten['name']) ?>" required>

            <label for="dob">Date of Birth:</label>
            <input type="date" name="dob" id="dob" value="<?= htmlspecialchars($kitten['dob']) ?>">

            <label for="gender">Gender:</label>
            <select name="gender" id="gender" required>
                <option value="">Select</option>
                <option value="Male" <?= $kitten['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $kitten['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
            </select>

            <label for="bio">Bio:</label>
            <textarea name="bio" id="bio" rows="5"><?= htmlspecialchars($kitten['bio']) ?></textarea>

            <label>Current Image:</label><br>
            <img src="<?= htmlspecialchars($kitten['image_path']) ?>" alt="" width="120"><br><br>

            <label for="image">Replace Image:</label>
            <input type="file" name="image" id="image" accept="image/*">

            <label>
                <input type="checkbox" name="available" <?= $kitten['available'] ? 'checked' : '' ?>>
                Available for reservation
            </label>

            <button type="submit">Save Changes</button>
        </form>
    </main>
</body>
</html>
