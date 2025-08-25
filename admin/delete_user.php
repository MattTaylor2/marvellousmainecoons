<?php
session_start();
require_once '/var/www/marvellousmainecoons/includes/config.php';
require_once '/var/www/marvellousmainecoons/includes/csrf.php';

function log_csrf_failure($username) {
    $log_entry = date('Y-m-d H:i:s') . " CSRF failure on delete_user.php for user: " . $username . "\n";
    error_log($log_entry, 3, '/var/log/marvellous/csrf_audit.log');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        log_csrf_failure($username);
        http_response_code(403);
        echo "CSRF validation failed.";
        exit;
    }

    if (empty($username)) {
        echo "Username is required.";
        exit;
    }

    // Connect to the database
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        error_log("DB connection failed: " . $mysqli->connect_error);
        echo "Database connection error.";
        exit;
    }

    // Prepare and execute deletion
    $stmt = $mysqli->prepare("DELETE FROM users WHERE username = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $mysqli->error);
        echo "Query preparation failed.";
        exit;
    }

    $stmt->bind_param("s", $username);
    if ($stmt->execute()) {
        echo "User '$username' deleted successfully.";
    } else {
        error_log("Execution failed: " . $stmt->error);
        echo "Failed to delete user.";
    }

    $stmt->close();
    $mysqli->close();
} else {
    http_response_code(405);
    echo "Method not allowed.";
}
?>

