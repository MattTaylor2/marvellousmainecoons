<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: /login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <h2>Admin Dashboard</h2>
    <p class="description">Welcome, Jules! Use the tools below to manage kitten profiles.</p>

    <ul>
        <li><a href="/tools/add-kitten.php">Add New Kitten</a></li>
        <li><a href="/tools/manage-kittens.php">Manage Existing Kittens</a></li>
        <li><a href="/auth/logout.php">Logout</a></li>
    </ul>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
