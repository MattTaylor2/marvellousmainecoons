<?php
require_once '../includes/functions.php';
require_once '../includes/csrf.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    logAudit('csrf_failure', $_SESSION['user_id'], 'Image edit blocked due to CSRF failure');
    exit('Invalid CSRF token');
}

$imageId = intval($_POST['image_id'] ?? 0);
$caption = trim($_POST['caption'] ?? '');
$placement = $_POST['placement'] ?? 'gallery';

$db = getDb();
$stmt = $db->prepare("UPDATE media SET caption = ?, placement = ? WHERE id = ?");
$stmt->execute([$caption, $placement, $imageId]);

logAudit('image_edit', $_SESSION['user_id'], "Edited image ID $imageId");

header('Location: media_manager.php?edited=1');
exit;
