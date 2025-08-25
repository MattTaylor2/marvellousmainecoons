<?php
session_start();
require_once '/var/www/marvellousmainecoons/includes/csrf.php';

function log_csrf_failure($action) {
    $log_entry = date('Y-m-d H:i:s') . " CSRF failure on admin/index.php for action: $action\n";
    error_log($log_entry, 3, '/var/log/marvellous/csrf_audit.log');
}

function log_admin_action($action) {
    $log_entry = date('Y-m-d H:i:s') . " Admin index action: $action\n";
    error_log($log_entry, 3, '/var/log/marvellous/admin_actions.log');
}

if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
    header("Location: /admin/authenticate.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        log_csrf_failure($action);
        http_response_code(403);
        echo "CSRF validation failed.";
        exit;
    }

    if ($action === 'toggle_site') {
        $flag_file = '/var/www/marvellousmainecoons/includes/site_disabled.flag';
        if (file_exists($flag_file)) {
            unlink($flag_file);
            log_admin_action('Site enabled');
            echo "Site enabled.";
        } else {
            file_put_contents($flag_file, 'disabled');
            log_admin_action('Site disabled');
            echo "Site disabled.";
        }
    } else {
        echo "Unknown action.";
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Home</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></h1>

    <section>
        <h2>Quick Links</h2>
        <ul>
            <li><a href="/admin/dashboard.php">Dashboard</a></li>
            <li><a href="/admin/users.php">Manage Users</a></li>
            <li><a href="/admin/manage_inquiries.php">Inquiries</a></li>
            <li><a href="/admin/feedback_moderation.php">Feedback</a></li>
            <li><a href="/admin/new_arrival.php">Add New Arrival</a></li>
            <li><a href="/admin/save_text.php">Edit Homepage Text</a></li>
        </ul>
    </section>

    <section>
        <h2>Site Controls</h2>
        <form method="POST" action="/admin/index.php">
            <input type="hidden" name="action" value="toggle_site">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
            <button type="submit">
                <?php
                $flag_file = '/var/www/marvellousmainecoons/includes/site_disabled.flag';
                echo file_exists($flag_file) ? "Enable Site" : "Disable Site";
                ?>
            </button>
        </form>
    </section>
</body>
</html>
