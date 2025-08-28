<?php
// 0. Show PHP errors (for debugging only – remove in prod)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Buffer all output
ob_start();

// 2. Send JSON header up front
header('Content-Type: application/json; charset=utf-8');

try {
    // 3. Bootstrap
    session_start();
    require_once __DIR__ . '/../includes/config.php';
    require_once __DIR__ . '/../includes/csrf.php';
    require_once __DIR__ . '/../includes/audit.php';

    // 4. Method & Auth guard
    if ($_SERVER['REQUEST_METHOD'] !== 'POST'
        || empty($_SESSION['authenticated'])
        || $_SESSION['role'] !== 'admin'
    ) {
        throw new RuntimeException('Unauthorized', 403);
    }

    // 5. CSRF guard
    $token = $_POST['csrf_token'] ?? '';
    if (! validate_csrf_token($token)) {
        throw new RuntimeException('Invalid CSRF token', 400);
    }

    // 6. File upload guard
    if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No file or upload error', 400);
    }

    // 7. Extension whitelist
    $origName    = basename($_FILES['image']['name']);
    $ext         = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $allowedExts = ['jpg','jpeg','png','gif'];
    if (! in_array($ext, $allowedExts, true)) {
        throw new RuntimeException('Invalid file type', 400);
    }

    // 8. Sanitize + uniquify filename
    $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($origName, PATHINFO_FILENAME));
    $safeName = sprintf('%s_%s.%s', time(), $safeBase, $ext);

    // 9. Move uploaded file
    $uploadDir   = realpath(__DIR__ . '/../uploads');
    $destination = $uploadDir . '/' . $safeName;
    if (! move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
        throw new RuntimeException('Failed to move file', 500);
    }

    // 10. Audit
    audit("Uploaded image {$safeName}", $_SESSION['user_id']);

    // 11. Clean buffer and send success JSON
    ob_clean();
    echo json_encode([
        'success'  => true,
        'filename' => $safeName,
        'url'      => '../uploads/' . $safeName
    ]);
    exit;
}
catch (Throwable $e) {
    // On any error or exception, clean buffer and return JSON error
    ob_clean();
    $code = $e->getCode() >= 100 && $e->getCode() < 600 ? $e->getCode() : 500;
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
    exit;
}
