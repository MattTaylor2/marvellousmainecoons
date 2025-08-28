<?php
session_start();

// Access check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Kittens</title>
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background: #fdfdfd; }
        h1 { color: #444; }
        .kitten-list { margin-top: 1rem; }
        .kitten-card {
            border: 1px solid #ccc;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            background: #fff;
        }
    </style>
</head>
<body>
    <h1>🐾 Manage Kittens</h1>
    <div class="kitten-list">
        <div class="kitten-card">
            <strong>Name:</strong> Cooper<br>
            <strong>Age:</strong> 12 weeks<br>
            <strong>Status:</strong> Available
        </div>
        <!-- More kittens will be loaded here dynamically -->
    </div>
</body>
</html>
