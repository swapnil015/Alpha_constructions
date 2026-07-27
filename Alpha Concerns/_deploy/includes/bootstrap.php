<?php
/**
 * Bootstrap — included by every entry point (public router, admin router, API).
 */
define('ALPHA_BOOTSTRAP', true);

require __DIR__ . '/config.php';
require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/csrf.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/mailer.php';
require __DIR__ . '/uploads.php';

// Sessions
session_name(SESSION_NAME);
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
