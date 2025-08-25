<?php
session_start();

// If not already set, initialize session values
if (!isset($_SESSION['test_flag'])) {
    $_SESSION['test_flag'] = 'session_is_working';
    echo "Session initialized. Reload this page.";
    exit;
}

// If already set, show session contents
echo '<h1>Session Test</h1>';
echo '<pre>'; print_r($_SESSION); echo '</pre>';
?>
