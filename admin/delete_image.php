<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/audit.php';

if (empty($_SESSION['authenticated']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD']==='POST'
    && validate_csrf_token($_POST['csrf_token'] ?? '')
) {
    $file = basename($_POST['filename'] ?? '');
    $path = realpath(__DIR__ . '/../uploads') . "/$file";
    if (file_exists($path)) {
        unlink($path);
        audit("Deleted image $file", $_SESSION['user_id']);
    }
}

header('Location: media_manager.php');
exit;
