<?php
// show errors (remove in prod)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false, // flip to true under HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// if already logged in, skip the login form
if (! empty($_SESSION['authenticated'])) {
    header('Location: admin_dashboard.php');
    exit;
}

require_once __DIR__ . '/includes/csrf.php';
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <style>
    /* ... your sparkle CSS ... */
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Admin Login</h2>

    <?php if (isset($_GET['error'])): ?>
      <div class="error">
        <?= htmlspecialchars($_GET['error']) ?>
      </div>
    <?php endif; ?>

    <form method="post" action="process_login.php">
      <input type="hidden" name="csrf_token"
             value="<?= htmlspecialchars($csrf_token) ?>">

      <label for="username">Username</label>
      <input type="text" id="username" name="username" required
             value="<?= isset($_GET['user']) ? htmlspecialchars($_GET['user']) : '' ?>">

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>

      <button type="submit">Log In</button>
    </form>
  </div>
</body>
</html>
