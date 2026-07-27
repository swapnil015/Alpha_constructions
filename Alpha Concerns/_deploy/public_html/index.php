<?php
/**
 * Alpha Concern — Public Front Controller
 */
require __DIR__ . '/../includes/bootstrap.php';

// Resolve current path
$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = '/' . trim($uri, '/');
$is404 = !empty($_GET['_404']);

// Process redirects table
if (!$is404) {
    try {
        $redir = db_one('SELECT to_url, type FROM redirects WHERE from_path = ? AND is_active = 1', [$path]);
        if ($redir) redirect($redir['to_url'], (int)$redir['type']);
    } catch (Throwable $e) { /* tolerate before install */ }
}

// Routes — string matches; regex for dynamic params
$routes = [
    '/'                          => ['home',                    'Home'],
    '/about'                     => ['about',                   'About Us'],
    '/services'                  => ['services/index',          'Services'],
    '/projects'                  => ['projects/index',          'Projects'],
    '/why-us'                    => ['why-us',                  'Why Choose Us'],
    '/blog'                      => ['blog/index',              'Insights & News'],
    '/careers'                   => ['careers',                 'Careers'],
    '/contact'                   => ['contact',                 'Contact Us'],
    '/privacy'                   => ['privacy',                 'Privacy Policy'],
];

if ($is404) {
    http_response_code(404);
    view('404', ['page_title' => 'Page Not Found']);
    exit;
}

// Static routes
if (isset($routes[$path])) {
    [$tpl, $title] = $routes[$path];
    view($tpl, ['page_title' => $title]);
    exit;
}

// Dynamic: /services/{slug}
if (preg_match('#^/services/([a-z0-9-]+)$#', $path, $m)) {
    $svc = db_one('SELECT * FROM services WHERE slug = ? AND is_active = 1', [$m[1]]);
    if (!$svc) { http_response_code(404); view('404', ['page_title'=>'Not Found']); exit; }
    view('services/detail', ['service' => $svc, 'page_title' => $svc['title']]);
    exit;
}

// Dynamic: /projects/{slug}
if (preg_match('#^/projects/([a-z0-9-]+)$#', $path, $m)) {
    $project = db_one('SELECT * FROM projects WHERE slug = ? AND is_published = 1', [$m[1]]);
    if (!$project) { http_response_code(404); view('404', ['page_title'=>'Not Found']); exit; }
    $images = db_all('SELECT * FROM project_images WHERE project_id = ? ORDER BY type, sort_order', [$project['id']]);
    view('projects/detail', ['project' => $project, 'images' => $images, 'page_title' => $project['title']]);
    exit;
}

// Dynamic: /blog/{slug}
if (preg_match('#^/blog/([a-z0-9-]+)$#', $path, $m)) {
    $post = db_one("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'", [$m[1]]);
    if (!$post) { http_response_code(404); view('404', ['page_title'=>'Not Found']); exit; }
    view('blog/detail', ['post' => $post, 'page_title' => $post['title']]);
    exit;
}

// Sitemap (kept here for cPanel simplicity)
if ($path === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    require VIEWS_PATH . '/sitemap.php';
    exit;
}

// robots.txt — dynamic so sitemap URL always matches host
if ($path === '/robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\n";
    echo "Allow: /\n";
    echo "Disallow: /admin/\n";
    echo "Disallow: /api/\n";
    echo "Disallow: /uploads/cvs/\n\n";
    echo "Sitemap: " . url('/sitemap.xml') . "\n";
    exit;
}

// Fall-through 404
http_response_code(404);
view('404', ['page_title' => 'Page Not Found']);
