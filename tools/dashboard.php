<?php
session_start();

// Access check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>";
    exit;
}

// Optional debug flag
$_SESSION['test_flag'] = 'session_is_working';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 2rem;
            background-color: #f9f9f9;
        }
        h1 {
            color: #333;
        }
        .section {
            background: #fff;
            border: 1px solid #ddd;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 8px;
        }
        pre {
            background: #eee;
            padding: 1rem;
            border-radius: 6px;
            overflow-x: auto;
        }
        .check {
            font-size: 1.1rem;
            color: green;
        }
    </style>
</head>
<body>
    <h1>🐾 Admin Dashboard</h1>

    <div class="section">
        <h2>Session Dump</h2>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>

    <div class="section">
        <h2>Access Check</h2>
        <ul>
            <li class="check">✅ user_id is set</li>
            <li class="check">✅ role is set: <?php echo htmlspecialchars($_SESSION['role']); ?></li>
            <li class="check">✅ role is admin</li>
        </ul>
    </div>

    <div class="section">
        <h2>Next Steps</h2>
        <p>Welcome Jules! You can now:</p>
        <ul>
            <li><a href="/tools/manage-kittens.php">🐱 Manage Kitten Profiles</a></li>
            <li><a href="/tools/upload-images.php">🖼️ Upload Kitten Images</a></li>
            <li><a href="/tools/edit-bios.php">📝 Edit Kitten Bios</a></li>
        </ul>
    </div>
</body>
</html>
