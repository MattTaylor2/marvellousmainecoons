<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load audit logging
require_once __DIR__ . '/../includes/audit.php';

// Log logout event if user was logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    log_event('logout', "User {$_SESSION['username']} logged out");
}

// Clear session data
$_SESSION = [];

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to homepage
header('Location: /');
exit;
