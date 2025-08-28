<?php
session_start();
require_once '/var/www/marvellousmainecoons/includes/csrf.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete User</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <h1>Delete User</h1>
    <form method="POST" action="/admin/delete_user.php">
        <label for="username">Username to delete:</label>
        <input type="text" id="username" name="username" required>

        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">

        <button type="submit">Delete User</button>
    </form>
</body>
</html>

