<?php
session_start();

// Dispatch based on request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validate CSRF token
    if (empty($_POST['csrf_token']) 
        || !hash_equals($_SESSION['csrf_token_change_pwd'], $_POST['csrf_token'])
    ) {
        http_response_code(400);
        echo 'Invalid CSRF token.';
        exit;
    }

    // 2. Retrieve and trim inputs
    $current = trim($_POST['current']  ?? '');
    $new     = trim($_POST['new']      ?? '');
    $confirm = trim($_POST['confirm']  ?? '');

    // 3. Server-side validation
    if (strlen($new) < 12) {
        echo 'New password must be at least 12 characters.';
        exit;
    }
    if ($new !== $confirm) {
        echo 'New passwords do not match.';
        exit;
    }

    // 4. Connect to MySQL (adjust credentials & host as needed)
    $mysqli = new mysqli('localhost', 'dbuser', 'dbpass', 'dbname');
    if ($mysqli->connect_error) {
        error_log('MySQL connect error: ' . $mysqli->connect_error);
        echo 'Server error.';
        exit;
    }

    // 5. Verify user is logged in
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        echo 'You must be logged in.';
        exit;
    }

    // 6. Fetch stored password hash
    $stmt = $mysqli->prepare('SELECT password_hash FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($storedHash);
    if (!$stmt->fetch()) {
        echo 'User not found.';
        $stmt->close();
        exit;
    }
    $stmt->close();

    // 7. Verify current password
    if (!password_verify($current, $storedHash)) {
        echo 'Current password is incorrect.';
        exit;
    }

    // 8. Hash new password & update
    $newHash = password_hash($new, PASSWORD_DEFAULT);
    $stmt    = $mysqli->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $stmt->bind_param('si', $newHash, $userId);
    if (!$stmt->execute()) {
        error_log('Password update failed: ' . $stmt->error);
        echo 'Server error.';
        $stmt->close();
        exit;
    }
    $stmt->close();

    // 9. Audit log entry
    $stmt  = $mysqli->prepare(
        'INSERT INTO audit_logs (user_id, action, timestamp) VALUES (?, ?, NOW())'
    );
    $action = 'password_change';
    $stmt->bind_param('is', $userId, $action);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();

    // 10. Rotate CSRF token to prevent replay
    $_SESSION['csrf_token_change_pwd'] = bin2hex(random_bytes(32));

    // 11. Success response (or redirect)
    echo 'Password updated successfully.';
    exit;
}

// GET request: ensure CSRF token exists
if (empty($_SESSION['csrf_token_change_pwd'])) {
    $_SESSION['csrf_token_change_pwd'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token_change_pwd'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Change Password</title>
  <script>
    // Client-side validation: min length + match
    function validateForm() {
      const nw  = document.getElementById('new').value;
      const cnf = document.getElementById('confirm').value;
      if (nw.length < 12) {
        alert('New password must be at least 12 characters.');
        return false;
      }
      if (nw !== cnf) {
        alert('New passwords do not match.');
        return false;
      }
      return true;
    }
  </script>
</head>
<body>
  <h1>Change Your Password</h1>
  <form action="change-password.php" method="POST" onsubmit="return validateForm()">
    <label for="current">Current Password:</label><br>
    <input type="password" id="current" name="current" required><br><br>

    <label for="new">New Password:</label><br>
    <input type="password" id="new" name="new" required><br><br>

    <label for="confirm">Confirm New Password:</label><br>
    <input type="password" id="confirm" name="confirm" required><br><br>

    <input 
      type="hidden" 
      name="csrf_token" 
      value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES); ?>"
    >

    <button type="submit">Update Password</button>
  </form>
</body>
</html>
