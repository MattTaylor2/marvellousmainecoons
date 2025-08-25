<?php
// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require login and helpers
require_once __DIR__ . '/../includes/audit.php';
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    log_event('unauthorized_access', 'Feedback page accessed without login');
    header('Location: /login.php');
    exit;
}

// Log access
log_event('feedback_viewed', "User {$_SESSION['username']} viewed feedback");

// Connect to DB
$pdo = get_db_connection();

// Fetch feedback entries
$stmt = $pdo->query('SELECT id, name, email, message, submitted_at FROM feedback ORDER BY submitted_at DESC');
$feedback_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Feedback - Marvellous Maine Coons</title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
    <header>
        <h1>Visitor Feedback</h1>
        <nav>
            <a href="/tools/dashboard.php">Dashboard</a>
            <a href="/tools/manage_kittens.php">Manage Kittens</a>
            <a href="/tools/logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <section class="feedback-list">
            <h2>Messages from Visitors</h2>
            <?php if (count($feedback_entries) === 0): ?>
                <p>No feedback submitted yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedback_entries as $entry): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($entry['submitted_at']); ?></td>
                                <td><?php echo htmlspecialchars($entry['name']); ?></td>
                                <td><?php echo htmlspecialchars($entry['email']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($entry['message'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Marvellous Maine Coons</p>
    </footer>
</body>
</html>
