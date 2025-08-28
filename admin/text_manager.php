<?php
session_start();
if (empty($_SESSION['authenticated']) || $_SESSION['role']!=='admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/csrf.php';
$csrf = generate_csrf_token();

// find all .txt under includes
$dir   = realpath(__DIR__ . '/../includes');
$files = array_map('basename', glob("$dir/*.txt"));
?>
<!DOCTYPE html>
<html lang="en"><head>
  <meta charset="UTF-8">
  <title>Text Manager</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
</head><body>
  <?php include __DIR__ . '/../includes/admin-header.php'; ?>

  <h1>Manage Text Files</h1>
  <ul>
    <?php foreach ($files as $f): ?>
      <li>
        <a href="save_text.php?file=<?=urlencode($f)?>">
          <?=htmlspecialchars($f)?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

  <p><a href="dashboard.php">← Back to Dashboard</a></p>
  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body></html>
