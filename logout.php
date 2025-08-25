<?php
declare(strict_types=1);
session_start();

// ✅ Log the logout event
function log_logout(string $username): void {
    $log_entry = date('Y-m-d H:i:s') . " Logout for user: " . $username . "\n";
    error_log($log_entry, 3, '/var/log/marvellous/admin_actions.log');
}

// ✅ Get username from session (fallback to 'unknown')
$username = $_SESSION['admin_username'] ?? $_SESSION['user_id'] ?? 'unknown';
log_logout((string)$username);

// ✅ Unset all session variables
$_SESSION = [];

// ✅ Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ✅ Destroy the session
session_destroy();

// ✅ Redirect to login page
header("Location: /login.php");
exit;

