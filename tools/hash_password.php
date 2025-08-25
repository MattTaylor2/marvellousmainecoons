<?php
// CLI only
if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

require_once '/var/www/marvellousmainecoons/includes/config.php';

function log_hash_usage($status, $input) {
    $log_entry = date('Y-m-d H:i:s') . " hash_password.php [$status] input: " . $input . "\n";
    error_log($log_entry, 3, '/var/log/marvellous/admin_actions.log');
}

if ($argc !== 2) {
    echo "Usage: php hash_password.php \"your_password\"\n";
    exit(1);
}

$password = trim($argv[1]);

if (empty($password)) {
    log_hash_usage("FAILURE", "[empty]");
    echo "Error: Password cannot be empty.\n";
    exit(1);
}

$hashed = password_hash($password, PASSWORD_ARGON2ID);

if ($hashed === false) {
    log_hash_usage("FAILURE", $password);
    echo "Error: Failed to hash password.\n";
    exit(1);
}

log_hash_usage("SUCCESS", $password);
echo "Hashed password:\n$hashed\n";
exit(0);

