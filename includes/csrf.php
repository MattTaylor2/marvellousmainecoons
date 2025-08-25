<?php
// Make sure a session is active
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * generate_csrf_token
 *   - Creates a random token, stores it in session, and returns it.
 *   - Token is single-use.
 */
function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * validate_csrf_token
 *   - Compares posted token to session token.
 *   - Returns true on match and clears the session token.
 */
function validate_csrf_token(string $token): bool
{
    if (
        ! empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token)
    ) {
        // single-use token
        unset($_SESSION['csrf_token']);
        return true;
    }
    return false;
}


// Generates or retrieves a CSRF token
function csrf_token_change_pwd() {
    if (empty($_SESSION['csrf_token_change_pwd'])) {
        $_SESSION['csrf_token_change_pwd'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token_change_pwd'];
}

// Validates the CSRF token, sends HTTP 400 on failure
function csrf_check_change_pwd($token) {
    if (empty($token)
        || !hash_equals($_SESSION['csrf_token_change_pwd'], $token)
    ) {
        http_response_code(400);
        echo 'Invalid CSRF token.';
        exit;
    }
}
