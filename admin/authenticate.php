<?php
session_start();
require_once '/var/www/marvellousmainecoons/includes/config.php';
require_once '/var/www/marvellousmainecoons/includes/csrf.php';

function log_csrf_failure($username) {
    $log_entry = date('Y-m-d H:i:s') . " CSRF failure on authenticate.php for user: $username\n";
    error_log($log_entry, 3, '/var/log/marvellous/csrf_audit.log');
}

function log_login_attempt($username, $success) {
    $status = $success ? "SUCCESS" : "FAILURE";
    $log_entry = date('Y-m-d H:i:s') . " Login $status for user: $username\n";
    error_log($log_entry, 3, '/var/log/marvellous/admin_actions.log');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        log_csrf_failure($username);
        http_response_code(403);
        echo "CSRF validation failed.";
        exit;
    }

    if (empty($username) || empty($password)) {
        echo "Username and password are required.";
        exit;
    }

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        error_log("DB connection failed: " . $mysqli->connect_error);
        echo "Database connection error.";
        exit;
    }

    $stmt = $mysqli->prepare("SELECT password, role FROM users WHERE username = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $mysqli->error);
        echo "Query preparation failed.";
        $mysqli->close();
        exit;
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password) && $role === 'admin') {
            $_SESSION['admin_authenticated'] = true;
            $_SESSION['admin_username'] = $username;
            log_login_attempt($username, true);
            header("Location: /admin/dashboard.php");
            exit;
        } else {
            log_login_attempt($username, false);
            echo "Invalid credentials or insufficient privileges.";
        }
    } else {
        log_login_attempt($username, false);
        echo "User not found.";
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
    <title>Admin Login</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <h1>Admin Login</h1>
    <form method="POST" action="/admin/authenticate.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">

        <button type="submit">Login</button>
    </form>
</body>
</html>
