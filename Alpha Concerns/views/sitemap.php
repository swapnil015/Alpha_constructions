<?php
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$static = ['/','/about','/services','/projects','/why-us','/blog','/careers','/contact','/privacy'];
foreach ($static as $p) {
    echo "  <url><loc>" . e(url($p)) . "</loc><changefreq>weekly</changefreq></url>\n";
}
foreach (db_all("SELECT slug, updated_at FROM projects WHERE is_published=1") as $r) {
    echo "  <url><loc>" . e(url('/projects/' . $r['slug'])) . "</loc><lastmod>" . date('Y-m-d', strtotime($r['updated_at'])) . "</lastmod></url>\n";
}
foreach (db_all("SELECT slug FROM services WHERE is_active=1") as $r) {
    echo "  <url><loc>" . e(url('/services/' . $r['slug'])) . "</loc></url>\n";
}
foreach (db_all("SELECT slug, updated_at FROM blog_posts WHERE status='published'") as $r) {
    echo "  <url><loc>" . e(url('/blog/' . $r['slug'])) . "</loc><lastmod>" . date('Y-m-d', strtotime($r['updated_at'])) . "</lastmod></url>\n";
}
echo '</urlset>';
