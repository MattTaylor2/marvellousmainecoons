<?php
session_start();

// Redirect if not logged in or not an admin
if (empty($_SESSION['authenticated']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard – Marvellous Maine Coons</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <style>
    .admin-nav { margin: 20px 0; }
    .admin-nav ul { list-style: none; padding: 0; display: flex; gap: 12px; }
    .admin-nav a { text-decoration: none; color: #333; padding: 6px 12px; border: 1px solid #ccc; border-radius: 4px; }
    .admin-nav a:hover { background: #f0f0f0; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../includes/admin-header.php'; ?>

  <h1>Welcome to Your Admin Dashboard</h1>

  <nav class="admin-nav">
    <ul>
      <li><a href="dashboard.php">Home</a></li>
      <li><a href="media_manager.php">Manage Media</a></li>
      <li><a href="text_manager.php">Manage Text</a></li>
      <li><a href="users.php">Manage Users</a></li>
      <li><a href="manage_inquiries.php">View Inquiries</a></li>
      <li><a href="feedback_moderation.php">Moderate Feedback</a></li>
      <li><a href="reset_password.php">Reset Passwords</a></li>
      <li><a href="logout.php">Log Out</a></li>
    </ul>
  </nav>

  <section>
    <h2>Quick Stats</h2>
    <?php
      // Example: fetch some counts for quick glance
      $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
      $kittenCount = $db->query("SELECT COUNT(*) AS c FROM kittens")->fetch_object()->c;
      $userCount   = $db->query("SELECT COUNT(*) AS c FROM users")->fetch_object()->c;
      $inquiryCount = $db->query("SELECT COUNT(*) AS c FROM inquiries")->fetch_object()->c;
    ?>
    <ul>
      <li>Available Kittens: <?= $kittenCount ?></li>
      <li>Registered Users: <?= $userCount  ?></li>
      <li>Pending Inquiries: <?= $inquiryCount ?></li>
    </ul>
  </section>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
