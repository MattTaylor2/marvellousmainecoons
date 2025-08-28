<?php
session_start();
if (empty($_SESSION['authenticated']) || $_SESSION['role']!=='admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/audit.php';

$dir       = realpath(__DIR__ . '/../includes');
$allowed   = array_map('basename', glob("$dir/*.txt"));
$file      = $_GET['file'] ?? $_POST['file'] ?? '';
$file      = basename($file);

if (!in_array($file, $allowed)) {
    die('Invalid file.');
}

$path      = "$dir/$file";
$csrf_token= generate_csrf_token();

if ($_SERVER['REQUEST_METHOD']==='POST'
    && validate_csrf_token($_POST['csrf_token'] ?? '')
) {
    $content = $_POST['content'] ?? '';
    file_put_contents($path, $content);
    audit("Edited text file $file", $_SESSION['user_id']);
    header('Location: text_manager.php');
    exit;
}

$existing = file_get_contents($path);
?>
<!DOCTYPE html>
<html lang="en"><head>
  <meta charset="UTF-8">
  <title>Edit <?=htmlspecialchars($file)?></title>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <style>textarea{width:100%;height:400px;font-family:monospace;}</style>
</head><body>
  <?php include __DIR__ . '/../includes/admin-header.php'; ?>

  <h1>Editing <?=htmlspecialchars($file)?></h1>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($csrf_token)?>">
    <input type="hidden" name="file" value="<?=htmlspecialchars($file)?>">
    <textarea name="content"><?=htmlspecialchars($existing)?></textarea>
    <button>Save Changes</button>
  </form>

  <p><a href="text_manager.php">← Back to Text Manager</a></p>
  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
