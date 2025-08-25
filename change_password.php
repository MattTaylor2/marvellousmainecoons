<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf.php';

function log_password_change(string $username, string $status): void {
    $time  = date('Y-m-d H:i:s');
    $entry = "$time Password change $status for user: $username\n";
    error_log($entry, 3, '/var/log/marvellous/admin_actions.log');
}

if (empty($_SESSION['authenticated']) || empty($_SESSION['username'])) {
    header('Location: login.php?error=' . urlencode('Please log in first'));
    exit;
}

$error   = '';
$success = '';
$csrf_token = generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) CSRF validation
    if (! validate_csrf_token($_POST['csrf_token'] ?? '')) {
        log_password_change($_SESSION['username'], 'CSRF FAILURE');
        header(
          'Location: change_password.php?error='
          . urlencode('CSRF validation failed')
        );
        exit;
    }

    // 2) Gather & trim inputs
    $oldPwd  = trim($_POST['old_password']     ?? '');
    $newPwd  = trim($_POST['new_password']     ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    // 3) Basic validation
    if ($oldPwd === '' || $newPwd === '' || $confirm === '') {
        $error = 'All fields are required';
    } elseif ($newPwd !== $confirm) {
        $error = 'New passwords do not match';
    } elseif (strlen($newPwd) < 8) {
        $error = 'New password must be at least 8 characters';
    }

    // 4) If no errors, verify old password and update
    if ($error === '') {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($mysqli->connect_error) {
            $error = 'Database connection error';
        } else {
            $stmt = $mysqli->prepare(
                "SELECT password_hash
                   FROM users
                  WHERE username = ?"
            );
            $stmt->bind_param('s', $_SESSION['username']);
            $stmt->execute();
            $stmt->bind_result($hash);
            if ($stmt->fetch() && password_verify($oldPwd, $hash)) {
                $stmt->close();
                $newHash = password_hash($newPwd, PASSWORD_DEFAULT);

                $upd = $mysqli->prepare(
                    "UPDATE users
                        SET password_hash = ?
                      WHERE username = ?"
                );
                $upd->bind_param('ss', $newHash, $_SESSION['username']);
                if ($upd->execute() && $upd->affected_rows === 1) {
                    log_password_change($_SESSION['username'], 'SUCCESS');
                    $success = 'Password changed successfully';
                } else {
                    log_password_change($_SESSION['username'], 'DB FAILURE');
                    $error = 'Could not update password. Please try again.';
                }
                $upd->close();
            } else {
                log_password_change($_SESSION['username'], 'INVALID OLD PASSWORD');
                $error = 'Old password is incorrect';
                $stmt->close();
            }
            $mysqli->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Change Password</title>
  <style>
    /* your existing sparkle CSS… */
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Change Password</h2>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
      <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" action="change_password.php">
      <input type="hidden" name="csrf_token"
             value="<?= htmlspecialchars($csrf_token) ?>">
      <label for="old_password">Old Password</label>
      <input type="password" id="old_password" name="old_password" required>
      <label for="new_password">New Password</label>
      <input type="password" id="new_password" name="new_password" required>
      <label for="confirm_password">Confirm New Password</label>
      <input type="password" id="confirm_password"
             name="confirm_password" required>
      <button type="submit">Change Password</button>
    </form>
    <div class="back-link">
      <a href="login.php">Back to Login</a>
    </div>
  </div>
</body>
</html>
