<?php
session_start();
require_once __DIR__ . '/includes/config.php';

// ✅ Session check: only allow admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Access denied.";
    exit;
}

// ✅ Connect using PDO
try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    error_log("DB connection failed: " . $e->getMessage());
    echo "Database error.";
    exit;
}

// ✅ Fetch reservations
$stmt = $pdo->query("SELECT id, user_id, kitten, message, created_at FROM reservations ORDER BY created_at DESC");
$reservations = $stmt->fetchAll();

// ✅ Optional audit log
$log_entry = sprintf("[%s] admin:%d viewed reservations\n", date('Y-m-d H:i:s'), $_SESSION['user_id']);
file_put_contents('/var/log/admin_audit.log', $log_entry, FILE_APPEND);

// ✅ Output HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Reservations</title>
    <style>
        body { font-family: sans-serif; margin: 2em; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 0.5em; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Kitten Reservations</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Kitten</th>
                <th>Message</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservations as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['id']) ?></td>
                    <td><?= htmlspecialchars($r['user_id']) ?></td>
                    <td><?= htmlspecialchars($r['kitten']) ?></td>
                    <td><?= htmlspecialchars($r['message']) ?></td>
                    <td><?= htmlspecialchars($r['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
