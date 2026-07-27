<?php
/**
 * Alpha Concern — Site Configuration
 * Edit values below for your environment. Do NOT commit real credentials.
 */

if (!defined('ALPHA_BOOTSTRAP')) {
    http_response_code(403);
    exit('Forbidden');
}

// -----------------------------------------------------------------------------
// Environment
// -----------------------------------------------------------------------------
define('APP_ENV', 'production');               // 'development' | 'production'
define('APP_DEBUG', APP_ENV === 'development');

// -----------------------------------------------------------------------------
// Site
// -----------------------------------------------------------------------------
define('SITE_NAME', 'Alpha Concern');
define('SITE_TAGLINE', 'Building Tomorrow, Today');

// BASE_URL auto-detects from the actual host, so the same code works
// on alpha.aais.space (preview) and alphaconcern.com (production)
// without editing config. Override here only if you need a forced canonical.
$_isLocal = !empty($_SERVER['HTTP_HOST']) && (
    str_contains($_SERVER['HTTP_HOST'],'localhost') ||
    str_contains($_SERVER['HTTP_HOST'],'127.0.0.1')
);
$_scheme = $_isLocal ? 'http' : 'https';
$_host   = $_SERVER['HTTP_HOST'] ?? 'alphaconcern.com';
define('BASE_URL', $_scheme . '://' . $_host);
define('ADMIN_EMAIL', 'admin@alphaconcern.com');

// -----------------------------------------------------------------------------
// Database
// -----------------------------------------------------------------------------
// 'mysql' for cPanel production. 'sqlite' for local preview without MySQL.
define('DB_DRIVER', getenv('ALPHA_DB') ?: 'mysql');

// MySQL (cPanel → MySQL Databases)
define('DB_HOST', 'localhost');
define('DB_NAME', 'YOUR_DB_NAME');
define('DB_USER', 'YOUR_DB_USER');
define('DB_PASS', 'YOUR_DB_PASS');
define('DB_CHARSET', 'utf8mb4');

// SQLite (local preview only)
define('SQLITE_PATH', dirname(__DIR__) . '/database/preview.sqlite');

// -----------------------------------------------------------------------------
// Paths (auto-detected — usually no need to change)
// -----------------------------------------------------------------------------
define('ROOT_PATH', dirname(__DIR__));            // One level up from /includes
define('PUBLIC_PATH', ROOT_PATH . '/public_html');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('CV_STORAGE_PATH', ROOT_PATH . '/cvs');    // OUTSIDE web root
define('UPLOADS_URL', '/uploads');

// -----------------------------------------------------------------------------
// Security
// -----------------------------------------------------------------------------
define('SESSION_NAME', 'ALPHA_SESSID');
define('CSRF_TOKEN_NAME', 'csrf_token');
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 15);
define('FORM_RATE_LIMIT', 5);                     // per IP per hour
define('PASSWORD_BCRYPT_COST', 12);

// -----------------------------------------------------------------------------
// Mail (PHPMailer SMTP — drop PHPMailer in /includes/PHPMailer/ to enable)
// -----------------------------------------------------------------------------
define('SMTP_ENABLED', false);                    // Set true once configured
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@alphaconcern.com');
define('SMTP_PASS', '');
define('SMTP_FROM_EMAIL', 'noreply@alphaconcern.com');
define('SMTP_FROM_NAME', 'Alpha Concern');
define('SMTP_SECURE', 'tls');                     // 'tls' or 'ssl'

// -----------------------------------------------------------------------------
// Uploads
// -----------------------------------------------------------------------------
define('MAX_IMAGE_BYTES', 10 * 1024 * 1024);      // 10 MB
define('MAX_DOCUMENT_BYTES', 5 * 1024 * 1024);    // 5 MB
define('ALLOWED_IMAGE_MIME', ['image/jpeg','image/png','image/gif','image/webp']);
define('ALLOWED_DOC_MIME',   ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// -----------------------------------------------------------------------------
// Error reporting
// -----------------------------------------------------------------------------
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// -----------------------------------------------------------------------------
// Timezone
// -----------------------------------------------------------------------------
date_default_timezone_set('Asia/Kathmandu');
