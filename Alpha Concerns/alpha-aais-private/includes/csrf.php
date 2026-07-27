<?php
if (!defined('ALPHA_BOOTSTRAP')) { http_response_code(403); exit('Forbidden'); }

function csrf_token(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function csrf_field(): string {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . e(csrf_token()) . '">';
}

function csrf_check(?string $token): bool {
    return !empty($token)
        && !empty($_SESSION[CSRF_TOKEN_NAME])
        && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function csrf_verify_or_die(): void {
    $token = $_POST[CSRF_TOKEN_NAME] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!csrf_check($token)) {
        http_response_code(419);
        exit('Invalid or expired session token. Please refresh and try again.');
    }
}
