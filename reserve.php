<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (!($_SESSION['verified'] ?? false)) {
    die('Access denied. Please log in and verify your account.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        logAudit("CSRF failure on reservation", $_SESSION['username'] ?? 'unknown');
        die('Invalid CSRF token.');
    }

    $kitten = trim($_POST['kitten'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$kitten || !$message) {
        die('All fields are required.');
    }

    $pdo = getPDO();
    $stmt = $pdo->prepare("INSERT INTO reservations (user_id, kitten, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['user_id'], $kitten, $message]);

    logAudit("Reservation submitted", "User ID: {$_SESSION['user_id']}, Kitten: $kitten");
    echo "Reservation submitted successfully!";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head><title>Reserve a Kitten</title></head>
<body>
<h2>Reserve Your Kitten</h2>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCSRFToken()) ?>">
    <label>Kitten Name: <input type="text" name="kitten" required></label><br>
    <label>Message: <textarea name="message" rows="5" cols="40" required></textarea></label><br>
    <button type="submit">Submit Reservation</button>
</form>
</body>
</html>
