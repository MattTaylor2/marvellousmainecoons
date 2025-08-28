<?php
session_start();
require_once '/var/www/marvellousmainecoons/includes/config.php';
require_once '/var/www/marvellousmainecoons/includes/csrf.php';

function log_csrf_failure($action, $id) {
    $log_entry = date('Y-m-d H:i:s') . " CSRF failure on feedback_moderation.php for action: $action, ID: $id\n";
    error_log($log_entry, 3, '/var/log/marvellous/csrf_audit.log');
}

function log_feedback_action($action, $id) {
    $log_entry = date('Y-m-d H:i:s') . " Feedback $action for ID: $id\n";
    error_log($log_entry, 3, '/var/log/marvellous/admin_actions.log');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $feedback_id = $_POST['feedback_id'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        log_csrf_failure($action, $feedback_id);
        http_response_code(403);
        echo "CSRF validation failed.";
        exit;
    }

    if (empty($action) || empty($feedback_id)) {
        echo "Action and feedback ID are required.";
        exit;
    }

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        error_log("DB connection failed: " . $mysqli->connect_error);
        echo "Database connection error.";
        exit;
    }

    if ($action === 'approve') {
        $stmt = $mysqli->prepare("UPDATE feedback SET approved = 1 WHERE id = ?");
    } elseif ($action === 'delete') {
        $stmt = $mysqli->prepare("DELETE FROM feedback WHERE id = ?");
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

    $stmt->bind_param("i", $feedback_id);
    if ($stmt->execute()) {
        log_feedback_action($action, $feedback_id);
        echo "Feedback ID $feedback_id $action successful.";
    } else {
        error_log("Execution failed: " . $stmt->error);
        echo "Failed to $action feedback.";
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
    <title>Moderate Feedback</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <h1>Moderate Feedback</h1>
    <?php
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        echo "<p>Database connection error.</p>";
        exit;
    }

    $result = $mysqli->query("SELECT id, content, approved FROM feedback ORDER BY created_at DESC");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div style='margin-bottom:20px;'>";
            echo "<p><strong>ID:</strong> " . htmlspecialchars($row['id']) . "</p>";
            echo "<p><strong>Content:</strong> " . htmlspecialchars($row['content']) . "</p>";
            echo "<p><strong>Status:</strong> " . ($row['approved'] ? "Approved" : "Pending") . "</p>";
            echo "<form method='POST' action='/admin/feedback_moderation.php' style='display:inline-block; margin-right:10px;'>";
            echo "<input type='hidden' name='feedback_id' value='" . htmlspecialchars($row['id']) . "'>";
            echo "<input type='hidden' name='action' value='approve'>";
            echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars(generate_csrf_token()) . "'>";
            echo "<button type='submit'>Approve</button>";
            echo "</form>";
            echo "<form method='POST' action='/admin/feedback_moderation.php' style='display:inline-block;'>";
            echo "<input type='hidden' name='feedback_id' value='" . htmlspecialchars($row['id']) . "'>";
            echo "<input type='hidden' name='action' value='delete'>";
            echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars(generate_csrf_token()) . "'>";
            echo "<button type='submit'>Delete</button>";
            echo "</form>";
            echo "</div>";
        }
    } else {
        echo "<p>No feedback entries found.</p>";
    }

    $mysqli->close();
    ?>
</body>
</html>
