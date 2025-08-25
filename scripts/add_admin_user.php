<?php
// ✅ Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ✅ Connect to database
$pdo = new PDO('mysql:host=localhost;dbname=mainecoons;charset=utf8mb4', 'admin_user', 'your_secure_password', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// ✅ Define new admin user
$username = 'yourwife';
$email = 'yourwife@example.com';
$password = 'SuperSecurePassword123!';
$hash = password_hash($password, PASSWORD_ARGON2ID);

// ✅ Insert user
$stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
$stmt->execute([$username, $email, $hash]);

echo "Admin user created successfully.\n";
?>
