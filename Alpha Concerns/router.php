<?php
/**
 * Router for `php -S` built-in dev server.
 * Mimics .htaccess: serve real files; route /admin/* to admin/index.php;
 * everything else to public_html/index.php.
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve real static files (css, js, images, etc.) by reading them directly,
// because PHP's built-in server docroot != public_html.
$file = __DIR__ . '/public_html' . $path;
if ($path !== '/' && is_file($file) && !str_ends_with($path, '.php')) {
    $mime = match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'svg'  => 'image/svg+xml',
        'png'  => 'image/png',
        'jpg','jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
        'ico'  => 'image/x-icon',
        'xml'  => 'application/xml',
        'txt'  => 'text/plain',
        'woff2'=> 'font/woff2',
        default=> 'application/octet-stream',
    };
    header('Content-Type: ' . $mime);
    readfile($file);
    return true;
}

// Admin: any path under /admin/* not matching a real file → admin/index.php
if (preg_match('#^/admin(/|$)#', $path)) {
    $_SERVER['SCRIPT_NAME'] = '/admin/index.php';
    require __DIR__ . '/public_html/admin/index.php';
    return true;
}

// API: direct PHP file
if (preg_match('#^/api/([a-z0-9_-]+)\.php$#', $path, $m)) {
    $f = __DIR__ . '/public_html/api/' . $m[1] . '.php';
    if (is_file($f)) { require $f; return true; }
}

// Public: front controller
require __DIR__ . '/public_html/index.php';
