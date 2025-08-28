<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$username || !$password) {
        die('All fields are required.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Invalid email address.');
    }

    $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 day'));

    $pdo = getPDO();
    $stmt = $pdo->prepare("INSERT INTO users (name, email, username, password, verified, verify_token, token_expires) VALUES (?, ?, ?, ?, 0, ?, ?)");
    $stmt->execute([$name, $email, $username, $hashedPassword, $token, $expires]);

    // Send verification email
    $verifyLink = "https://yourdomain.com/verify.php?token=$token";
    $subject = "Verify your account";
    $message = "Hi $name,\n\nPlease verify your account by clicking the link below:\n$verifyLink\n\nThis link expires in 24 hours.";
    mail($email, $subject, $message, "From: no-reply@yourdomain.com");

    echo "Signup successful! Please check your email to verify your account.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head><title>Signup</title></head>
<body>
<h2>Create an Account</h2>
<form method="post">
    <label>Name: <input type="text" name="name" required></label><br>
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Username: <input type="text" name="username" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button type="submit">Sign Up</button>
</form>
</body>
</html>

