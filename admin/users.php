<?php
session_start();
require_once '/var/www/marvellousmainecoons/includes/config.php';
require_once '/var/www/marvellousmainecoons/includes/csrf.php';

function log_csrf_failure($action, $username) {
    $log_entry = date('Y-m-d H:i:s') . " CSRF failure on users.php for action: $action, user: $username\n";
    error_log($log_entry, 3, '/var/log/marvellous/csrf_audit.log');
}

function log_user_action($action, $username) {
    $log_entry = date('Y-m-d H:i:s') . " User $action: $username\n";
    error_log($log_entry, 3, '/var/log/marvellous/admin_actions.log');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $username = $_POST['username'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        log_csrf_failure($action, $username);
        http_response_code(403);
        echo "CSRF validation failed.";
        exit;
    }

    if (empty($action) || empty($username)) {
        echo "Action and username are required.";
        exit;
    }

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        error_log("DB connection failed: " . $mysqli->connect_error);
        echo "Database connection error.";
        exit;
    }

    if ($action === 'promote') {
        $stmt = $mysqli->prepare("UPDATE users SET role = 'admin' WHERE username = ?");
    } elseif ($action === 'disable') {
        $stmt = $mysqli->prepare("UPDATE users SET active = 0 WHERE username = ?");
    } else {
        echo "Invalid action.";
        $mysqli->close();
        exit;
    }

    if (!$stmt) {
        error_log("Prepare failed: " . $mysqli->error);
        echo "Query preparation failed.";
        $mysqli->close();
        exit;
    }

    $stmt->bind_param("s", $username);
    if ($stmt->execute()) {
        log_user_action($action, $username);
        echo "User '$username' $action successful.";
    } else {
        error_log("Execution failed: " . $stmt->error);
        echo "Failed to $action user.";
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
    <title>Manage Users</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <h1>Manage Users</h1>
    <?php
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        echo "<p>Database connection error.</p>";
        exit;
    }

    $result = $mysqli->query("SELECT username, role, active FROM users ORDER BY username ASC");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div style='margin-bottom:20px;'>";
            echo "<p><strong>Username:</strong> " . htmlspecialchars($row['username']) . "</p>";
            echo "<p><strong>Role:</strong> " . htmlspecialchars($row['role']) . "</p>";
            echo "<p><strong>Status:</strong> " . ($row['active'] ? "Active" : "Disabled") . "</p>";

            echo "<form method='POST' action='/admin/users.php' style='display:inline-block; margin-right:10px;'>";
            echo "<input type='hidden' name='username' value='" . htmlspecialchars($row['username']) . "'>";
            echo "<input type='hidden' name='action' value='promote'>";
            echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars(generate_csrf_token()) . "'>";
            echo "<button type='submit'>Promote to Admin</button>";
            echo "</form>";

            echo "<form method='POST' action='/admin/users.php' style='display:inline-block;'>";
            echo "<input type='hidden' name='username' value='" . htmlspecialchars($row['username']) . "'>";
            echo "<input type='hidden' name='action' value='disable'>";
            echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars(generate_csrf_token()) . "'>";
            echo "<button type='submit'>Disable User</button>";
            echo "</form>";

            echo "</div>";
        }
    } else {
        echo "<p>No users found.</p>";
    }

    $mysqli->close();
    ?>
</body>
</html>


