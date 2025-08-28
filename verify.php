<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$token = $_GET['token'] ?? '';
if (!$token || strlen($token) !== 64) {
    die('Invalid token.');
}

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT id, token_expires FROM users WHERE verify_token = ? AND verified = 0");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die('Invalid or expired token.');
}

if (strtotime($user['token_expires']) < time()) {
    die('Token has expired.');
}

$stmt = $pdo->prepare("UPDATE users SET verified = 1, verify_token = NULL, token_expires = NULL WHERE id = ?");
$stmt->execute([$user['id']]);

echo "Your account has been verified! You may now log in.";

