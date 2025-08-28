<?php
session_start();

// Dummy credentials for testing
$valid_username = 'jules';
$valid_password_hash = password_hash('mainecoonsrock', PASSWORD_ARGON2ID);

// Get submitted credentials
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Basic validation
if ($username === $valid_username && password_verify($password, $valid_password_hash)) {
    $_SESSION['admin_logged_in'] = true;
    header('Location: /admin.php');
    exit;
} else {
    echo "<h2>Login failed</h2><p>Invalid username or password.</p><p><a href='/login.php'>Try again</a></p>";
}
