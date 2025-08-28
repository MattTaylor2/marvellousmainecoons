<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Kitten Bios</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <h1>📝 Edit Kitten Bios</h1>
    <p>This tool will let Jules write and update kitten descriptions. Coming soon!</p>
</body>
</html>
