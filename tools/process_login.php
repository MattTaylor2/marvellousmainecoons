<?php
// Force secure session cookie parameters
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load CSRF and database functions
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/audit.php';

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    log_event('login_failed_csrf', 'CSRF token validation failed');
    http_response_code(403);
    exit('Invalid CSRF token');
}

// Validate input
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    log_event('login_failed_input', 'Missing username or password');
    http_response_code(400);
    exit('Missing credentials');
}

// Connect to database
$pdo = get_db_connection();

// Fetch user with role
$stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = :username LIMIT 1');
$stmt->execute(['username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password_hash'])) {
    log_event('login_failed_auth', "Failed login for username: $username");
    http_response_code(401);
    exit('Invalid username or password');
}

// Regenerate session ID to prevent fixation
session_regenerate_id(true);

// Store user info in session
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['logged_in'] = true;
$_SESSION['role'] = $user['role'];

// Log successful login
log_event('login_success', "User {$user['username']} logged in with role {$user['role']}");

// Redirect to admin dashboard
header('Location: /tools/dashboard.php');
exit;
