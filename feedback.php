<?php
session_start();
require_once '/var/www/marvellousmainecoons/includes/config.php';
require_once '/var/www/marvellousmainecoons/includes/csrf.php';

function log_csrf_failure($name) {
    $log_entry = date('Y-m-d H:i:s') . " CSRF failure on feedback.php for name: $name\n";
    error_log($log_entry, 3, '/var/log/marvellous/csrf_audit.log');
}

function log_feedback_submission($name) {
    $log_entry = date('Y-m-d H:i:s') . " Feedback submitted by: $name\n";
    error_log($log_entry, 3, '/var/log/marvellous/admin_actions.log');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        log_csrf_failure($name);
        http_response_code(403);
        echo "CSRF validation failed.";
        exit;
    }

    if (empty($name) || empty($email) || empty($message)) {
        echo "All fields are required.";
        exit;
    }

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        error_log("DB connection failed: " . $mysqli->connect_error);
        echo "Database connection error.";
        exit;
    }

    $stmt = $mysqli->prepare("INSERT INTO feedback (name, email, content, approved, created_at) VALUES (?, ?, ?, 0, NOW())");
    if (!$stmt) {
        error_log("Prepare failed: " . $mysqli->error);
        echo "Query preparation failed.";
        $mysqli->close();
        exit;
    }

    $stmt->bind_param("sss", $name, $email, $message);
    if ($stmt->execute()) {
        log_feedback_submission($name);
        echo "Thank you for your feedback!";
    } else {
        error_log("Execution failed: " . $stmt->error);
        echo "Failed to submit feedback.";
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
    <title>Leave Feedback</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <h1>Leave Feedback</h1>
    <form method="POST" action="/feedback.php">
        <label for="name">Your Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Your Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="message">Message:</label>
        <textarea id="message" name="message" rows="6" cols="60" required></textarea>

        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">

        <button type="submit">Submit Feedback</button>
    </form>
</body>
</html>

