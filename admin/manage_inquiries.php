<?php
session_start();
require_once '/var/www/marvellousmainecoons/includes/config.php';
require_once '/var/www/marvellousmainecoons/includes/csrf.php';

function log_csrf_failure($id) {
    $log_entry = date('Y-m-d H:i:s') . " CSRF failure on manage_inquiries.php for inquiry ID: $id\n";
    error_log($log_entry, 3, '/var/log/marvellous/csrf_audit.log');
}

function log_inquiry_reply($id) {
    $log_entry = date('Y-m-d H:i:s') . " Inquiry replied to: ID $id\n";
    error_log($log_entry, 3, '/var/log/marvellous/admin_actions.log');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inquiry_id = $_POST['inquiry_id'] ?? '';
    $reply = $_POST['reply'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        log_csrf_failure($inquiry_id);
        http_response_code(403);
        echo "CSRF validation failed.";
        exit;
    }

    if (empty($inquiry_id) || empty($reply)) {
        echo "Inquiry ID and reply are required.";
        exit;
    }

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        error_log("DB connection failed: " . $mysqli->connect_error);
        echo "Database connection error.";
        exit;
    }

    $stmt = $mysqli->prepare("UPDATE inquiries SET reply = ?, replied_at = NOW() WHERE id = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $mysqli->error);
        echo "Query preparation failed.";
        $mysqli->close();
        exit;
    }

    $stmt->bind_param("si", $reply, $inquiry_id);
    if ($stmt->execute()) {
        log_inquiry_reply($inquiry_id);
        echo "Reply sent for inquiry ID $inquiry_id.";
    } else {
        error_log("Execution failed: " . $stmt->error);
        echo "Failed to send reply.";
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
    <title>Manage Inquiries</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <h1>Manage Inquiries</h1>
    <?php
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        echo "<p>Database connection error.</p>";
        exit;
    }

    $result = $mysqli->query("SELECT id, name, email, message, reply FROM inquiries ORDER BY created_at DESC");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div style='margin-bottom:30px;'>";
            echo "<p><strong>ID:</strong> " . htmlspecialchars($row['id']) . "</p>";
            echo "<p><strong>Name:</strong> " . htmlspecialchars($row['name']) . "</p>";
            echo "<p><strong>Email:</strong> " . htmlspecialchars($row['email']) . "</p>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($row['message']) . "</p>";
            echo "<p><strong>Reply:</strong> " . ($row['reply'] ? htmlspecialchars($row['reply']) : "<em>Not yet replied</em>") . "</p>";

            echo "<form method='POST' action='/admin/manage_inquiries.php'>";
            echo "<input type='hidden' name='inquiry_id' value='" . htmlspecialchars($row['id']) . "'>";
            echo "<label for='reply_" . $row['id'] . "'>Reply:</label><br>";
            echo "<textarea id='reply_" . $row['id'] . "' name='reply' rows='4' cols='60' required></textarea><br>";
            echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars(generate_csrf_token()) . "'>";
            echo "<button type='submit'>Send Reply</button>";
            echo "</form>";
            echo "</div>";
        }
    } else {
        echo "<p>No inquiries found.</p>";
    }

    $mysqli->close();
    ?>
</body>
</html>
