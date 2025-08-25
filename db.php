<?php
$dsn      = 'mysql:host=localhost;dbname=mainecoons;charset=utf8mb4';
$dbUser   = 'your_db_user';
$dbPass   = 'your_db_password';

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die('DB connection failed: ' . $e->getMessage());
}
