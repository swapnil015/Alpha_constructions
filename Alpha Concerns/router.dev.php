<?php
/**
 * Local dev entry point for `php -S`. Not deployed to cPanel.
 *
 * Two jobs:
 *   1. Force the SQLite preview database (see includes/config.php DB_DRIVER).
 *   2. Fill the gaps between PHP's built-in server and Apache — directory
 *      indexes, .html files and nested .php scripts all resolve natively under
 *      cPanel, but the built-in server routes everything to the front
 *      controller unless we intervene first.
 *
 * Anything not handled here falls through to the production router.
 */

putenv('ALPHA_DB=sqlite');
$_ENV['ALPHA_DB'] = 'sqlite';

$docroot = __DIR__ . '/public_html';
$path    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$target  = $docroot . urldecode($path);

// Directory request → serve its index (mirrors Apache DirectoryIndex).
if (is_dir($target)) {
    foreach (['index.html', 'index.php'] as $index) {
        $candidate = rtrim($target, '/') . '/' . $index;
        if (is_file($candidate)) {
            // Redirect /scroll → /scroll/ so relative asset URLs resolve.
            if (!str_ends_with($path, '/')) {
                header('Location: ' . $path . '/', true, 301);
                return true;
            }
            if (str_ends_with($index, '.php')) {
                require $candidate;
            } else {
                header('Content-Type: text/html; charset=utf-8');
                readfile($candidate);
            }
            return true;
        }
    }
}

// Nested .php scripts (e.g. /scroll/frames.php) — run them in place.
if (str_ends_with($path, '.php') && is_file($target)) {
    require $target;
    return true;
}

// Static types the production router's MIME table doesn't cover.
if (is_file($target)) {
    $extra = [
        'html' => 'text/html; charset=utf-8',
        'htm'  => 'text/html; charset=utf-8',
        'json' => 'application/json',
        'avif' => 'image/avif',
        'woff' => 'font/woff',
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
    ];
    $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
    if (isset($extra[$ext])) {
        header('Content-Type: ' . $extra[$ext]);
        readfile($target);
        return true;
    }
}

return require __DIR__ . '/router.php';
