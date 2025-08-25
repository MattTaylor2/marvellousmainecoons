<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/audit.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /login.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name']      ?? '');
    $dob       = $_POST['dob']            ?? null;
    $gender    = $_POST['gender']         ?? '';
    $bio       = trim($_POST['bio']       ?? '');
    $available = isset($_POST['available']) ? 1 : 0;

    if ($name === '' || !in_array($gender, ['Male','Female'], true)) {
        $error = 'Name and valid gender are required.';
    } else {
        $stmt = getDb()->prepare("
            INSERT INTO kittens (name, dob, gender, bio, available)
            VALUES (:name, :dob, :gender, :bio, :available)
        ");
        $stmt->execute([
            ':name'      => $name,
            ':dob'       => $dob,
            ':gender'    => $gender,
            ':bio'       => $bio,
            ':available' => $available,
        ]);

        logAction($_SESSION['user_id'], "Added kitten: {$name}");
        header('Location: /admin.php?msg=Kitten+added');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Kitten</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <?php include __DIR__ . '/../header.php'; ?>
    <main class="admin-add-kitten">
        <h1>Add a New Kitten</h1>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST">
            <label>Name: <input name="name" required></label><br>
            <label>DOB: <input name="dob" type="date"></label><br>
            <label>Gender:
                <select name="gender" required>
                    <option value="">Choose…</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </label><br>
            <label>Bio:<br>
                <textarea name="bio" rows="4"></textarea>
            </label><br>
            <label>
                <input name="available" type="checkbox" checked>
                Available
            </label><br>
            <button type="submit">Add Kitten</button>
        </form>
    </main>
    <?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>
