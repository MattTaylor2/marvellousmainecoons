<?php
// show all errors (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf.php';

// audit logger
function log_login_attempt(string $user, bool $ok): void {
    $s = $ok ? 'SUCCESS' : 'FAILURE';
    error_log(date('Y-m-d H:i:s') . " Login $s for user: $user\n",
              3, '/var/log/marvellous/admin_actions.log');
}

// only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$user      = trim($_POST['username']   ?? '');
$pass      = $_POST['password']        ?? '';
$csrf_token= $_POST['csrf_token']      ?? '';

// CSRF
if (! validate_csrf_token($csrf_token)) {
    log_login_attempt($user, false);
    header('Location: login.php?error='
           . urlencode('CSRF validation failed')
           . '&user=' . urlencode($user));
    exit;
}

// require fields
if ($user === '' || $pass === '') {
    log_login_attempt($user, false);
    header('Location: login.php?error='
           . urlencode('Username and password are required')
           . '&user=' . urlencode($user));
    exit;
}

// lookup
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    log_login_attempt($user, false);
    header('Location: login.php?error='
           . urlencode('Database connection error'));
    exit;
}

$stmt = $mysqli->prepare("
    SELECT password_hash, role
      FROM users
     WHERE username = ?
");
if (! $stmt) {
    log_login_attempt($user, false);
    header('Location: login.php?error='
           . urlencode('Server error'));
    exit;
}

$stmt->bind_param('s', $user);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($hash, $role);
    $stmt->fetch();

    if (password_verify($pass, $hash)) {
        session_regenerate_id(true);
        $_SESSION['authenticated'] = true;
        $_SESSION['username']      = $user;
        $_SESSION['role']          = $role;

        log_login_attempt($user, true);
        // ← redirect to dashboard on success
        header('Location: admin_dashboard.php');
        exit;
    }
}

log_login_attempt($user, false);
$stmt->close();
$mysqli->close();

header('Location: login.php?error='
       . urlencode('Invalid credentials')
       . '&user=' . urlencode($user));
exit;
