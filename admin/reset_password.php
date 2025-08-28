<?php
session_start();
require_once '/var/www/marvellousmainecoons/includes/config.php';
require_once '/var/www/marvellousmainecoons/includes/csrf.php';

function log_csrf_failure($username) {
    $log_entry = date('Y-m-d H:i:s') . " CSRF failure on reset_password.php for user: " . $username . "\n";
    error_log($log_entry, 3, '/var/log/marvellous/csrf_audit.log');
}

function log_password_reset($username) {
    $log_entry = date('Y-m-d H:i:s') . " Password reset for user: " . $username . "\n";
    error_log($log_entry, 3, '/var/log/marvellous/admin_actions.log');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        log_csrf_failure($username);
        http_response_code(403);
        echo "CSRF validation failed.";
        exit;
    }

    if (empty($username) || empty($new_password)) {
        echo "Username and new password are required.";
        exit;
    }

    $hashed_password = password_hash($new_password, PASSWORD_ARGON2ID);
    if ($hashed_password === false) {
        echo "Password hashing failed.";
        exit;
    }

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        error_log("DB connection failed: " . $mysqli->connect_error);
        echo "Database connection error.";
        exit;
    }

    $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE username = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $mysqli->error);
        echo "Query preparation failed.";
        exit;
    }

    $stmt->bind_param("ss", $hashed_password, $username);
    if ($stmt->execute()) {
        log_password_reset($username);
        echo "Password reset successfully for '$username'.";
    } else {
        error_log("Execution failed: " . $stmt->error);
        echo "Failed to reset password.";
    }

    $stmt->close();
    $mysqli->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <h1>Reset User Password</h1>
    <form method="POST" action="/admin/reset_password.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required>

        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">

        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
