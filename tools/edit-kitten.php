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

$stmt = $db->prepare("SELECT * FROM kittens WHERE id = ?");
$stmt->execute([$id]);
$kitten = $stmt->fetch();

if (!$kitten) {
    http_response_code(404);
    echo "<h1>404 Not Found</h1><p>Kitten not found.</p>";
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $name = trim($_POST['name']);
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $bio = trim($_POST['bio']);
    $available = isset($_POST['available']) ? 1 : 0;

    if ($name === '') $errors[] = "Name is required.";
    if (!in_array($gender, ['Male', 'Female'])) $errors[] = "Invalid gender.";

    // Handle image replacement
    $imagePath = $kitten['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('kitten_', true) . '.' . $ext;
        $targetPath = __DIR__ . '/../images/uploads/' . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = $filename;
        } else {
            $errors[] = "Image upload failed.";
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE kittens SET name = ?, dob = ?, gender = ?, bio = ?, image_path = ?, available = ? WHERE id = ?");
        $stmt->execute([$name, $dob, $gender, $bio, $imagePath, $available, $id]);

        // Optional: logAction('edit_kitten', $_SESSION['admin_id'], $id);

        header("Location: manage_kittens.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Kitten</title>
    <link rel="stylesheet" href="/css/styles.css">
    <style>
        form {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background: #fefefe;
            border-radius: 8px;
            box-shadow: 0 0 6px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-top: 15px;
        }
        input, textarea, select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        img.preview {
            max-width: 100%;
            margin-top: 10px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <h2 style="text-align:center;">Edit Kitten Profile</h2>
        <form method="POST" enctype="multipart/form-data">
            <?php csrfInput(); ?>

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

            <label for="image">Replace Image:</label>
            <input type="file" name="image" id="image" accept="image/*">
            <?php if ($kitten['image_path']): ?>
                <img src="/images/uploads/<?= htmlspecialchars($kitten['image_path']) ?>" alt="Current Image" class="preview">
            <?php endif; ?>

            <label>
                <input type="checkbox" name="available" <?= $kitten['available'] ? 'checked' : '' ?>>
                Available for reservation
            </label>

            <button type="submit">Save Changes</button>

            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $e) echo htmlspecialchars($e) . "<br>"; ?>
                </div>
            <?php endif; ?>
        </form>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
