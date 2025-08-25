<?php
// header.php
// Common site header with navigation
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Marvellous Maine Coons</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
<header>
    <div class="logo">
        <a href="/index.php">
            <img src="/images/logo.png" alt="Marvellous Maine Coons Logo">
        </a>
    </div>
    <nav>
        <ul>
            <li><a href="/index.php">Home</a></li>
            <li><a href="/kittens.php">Kittens</a></li>
            <?php if (session_status() !== PHP_SESSION_ACTIVE) session_start(); ?>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li><a href="/admin.php">Admin</a></li>
                    <li><a href="/tools/dashboard.php">Dashboard</a></li>
                <?php endif; ?>
                <li><a href="/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="/login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main>
