<?php
session_start();
require_once '/var/www/marvellousmainecoons/includes/config.php';
require_once '/var/www/marvellousmainecoons/includes/csrf.php';

function log_csrf_failure($name) {
    $log_entry = date('Y-m-d H:i:s') . " CSRF failure on new_arrival.php for cat: " . $name . "\n";
    error_log($log_entry, 3, '/var/log/marvellous/csrf_audit.log');
}

function log_new_arrival($name) {
    $log_entry = date('Y-m-d H:i:s') . " New arrival added: " . $name . "\n";
    error_log($log_entry, 3, '/var/log/marvellous/admin_actions.log');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        log_csrf_failure($name);
        http_response_code(403);
        echo "CSRF validation failed.";
        exit;
    }

    if (empty($name) || empty($description)) {
        echo "Name and description are required.";
        exit;
    }

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        error_log("DB connection failed: " . $mysqli->connect_error);
        echo "Database connection error.";
        exit;
    }

    $stmt = $mysqli->prepare("INSERT INTO arrivals (name, description, created_at) VALUES (?, ?, NOW())");
    if (!$stmt) {
        error_log("Prepare failed: " . $mysqli->error);
        echo "Query preparation failed.";
        exit;
    }

    $stmt->bind_param("ss", $name, $description);
    if ($stmt->execute()) {
        log_new_arrival($name);
        echo "New arrival '$name' added successfully.";
    } else {
        error_log("Execution failed: " . $stmt->error);
        echo "Failed to add new arrival.";
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
    <title>Add New Arrival</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <h1>Add New Arrival</h1>
    <form method="POST" action="/admin/new_arrival.php">
        <label for="name">Cat Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea>

        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">

        <button type="submit">Add Arrival</button>
    </form>
</body>
</html>

