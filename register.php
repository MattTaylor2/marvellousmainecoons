<?php
// /var/www/marvellousmainecoons/register.php

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        log_csrf_failure("Register attempt");
        echo "Invalid CSRF token.";
        exit;
    }

    if (empty($username) || empty($email) || empty($password)) {
        echo "All fields are required.";
        exit;
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo "Email already registered.";
        exit;
    }

    // Hash password
    $hash = password_hash($password, PASSWORD_ARGON2ID);
    if (!$hash) {
        echo "Failed to hash password.";
        exit;
    }

    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, created_at) VALUES (?, ?, ?, 'customer', NOW())");
    $stmt->execute([$username, $email, $hash]);

    $user_id = $pdo->lastInsertId();
    error_log(date('Y-m-d H:i:s') . " register.php [SUCCESS] user_id: $user_id email: $email\n", 3, '/var/log/marvellous/admin_actions.log');

    // Log in user
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = 'customer';

    header('Location: /index.php');
    exit;
}

// Generate CSRF token for form
$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register – Marvellous Maine Coons</title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
    <main>
        <h1>Create an Account</h1>
        <form method="POST" action="/register.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <label>Username:<br><input type="text" name="username" required></label><br>
            <label>Email:<br><input type="email" name="email" required></label><br>
            <label>Password:<br><input type="password" name="password" required></label><br>
            <button type="submit">Register</button>
        </form>
    </main>
</body>
</html>
