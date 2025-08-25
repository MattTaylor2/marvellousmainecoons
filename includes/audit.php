<?php
/**
 * Logs an audit event to both a file and the database.
 *
 * @param string $message  A description of the event
 * @param int    $userId   The authenticated user's ID
 */
function audit(string $message, int $userId): void
{
    // 1) File‐based audit
    $timestamp = date('Y-m-d H:i:s');
    $line = "[$timestamp] User:$userId - $message\n";
    // __DIR__ = includes/, so go up one level to logs/
    file_put_contents(__DIR__ . '/../logs/audit.log', $line, FILE_APPEND | LOCK_EX);

    // 2) Database‐based audit
    require_once __DIR__ . '/config.php';  // for DB_HOST, DB_USER, etc.
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        // fail silently if DB is unreachable
        return;
    }
    $stmt = $mysqli->prepare(
      "INSERT INTO audit_trail (user_id, message, created_at)
       VALUES (?, ?, NOW())"
    );
    $stmt->bind_param('is', $userId, $message);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}
