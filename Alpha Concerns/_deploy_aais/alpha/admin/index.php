<?php
/**
 * Admin Front Controller — routes everything inside /admin/ except direct files.
 */
require __DIR__ . '/../../alpha_private/includes/bootstrap.php';

$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/admin/', PHP_URL_PATH);
$path = '/' . trim($uri, '/');

// Public admin pages (no auth)
$publicMap = [
    '/admin'            => 'login',
    '/admin/'           => 'login',
    '/admin/login'      => 'login',
    '/admin/login.php'  => 'login',
    '/admin/logout'     => 'logout',
    '/admin/logout.php' => 'logout',
];
if (isset($publicMap[$path])) {
    require __DIR__ . '/' . $publicMap[$path] . '.php';
    exit;
}

// Authenticated routes
auth_require();

$routes = [
    '/admin/dashboard'           => 'dashboard',
    '/admin/projects'            => 'projects/list',
    '/admin/projects/list'       => 'projects/list',
    '/admin/projects/new'        => 'projects/edit',
    '/admin/projects/edit'       => 'projects/edit',
    '/admin/projects/delete'     => 'projects/delete',
    '/admin/blog'                => 'blog/list',
    '/admin/blog/list'           => 'blog/list',
    '/admin/blog/new'            => 'blog/edit',
    '/admin/blog/edit'           => 'blog/edit',
    '/admin/blog/delete'         => 'blog/delete',
    '/admin/inquiries'           => 'inquiries',
    '/admin/inquiries/view'      => 'inquiries-view',
    '/admin/careers'             => 'careers',
    '/admin/careers/edit'        => 'careers-edit',
    '/admin/careers/applications'=> 'careers-applications',
    '/admin/careers/cv'          => 'careers-cv',
    '/admin/media'               => 'media',
    '/admin/settings'            => 'settings',
    '/admin/users'               => 'users',
];

if (isset($routes[$path])) {
    require __DIR__ . '/_pages/' . $routes[$path] . '.php';
    exit;
}

// Default → dashboard
redirect('/admin/dashboard');
