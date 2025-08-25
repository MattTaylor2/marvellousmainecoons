<?php
// Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'marvellousmainecoons');
define('DB_USER', 'matt');
define('DB_PASS', 'Julie-Anne220964');

try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
