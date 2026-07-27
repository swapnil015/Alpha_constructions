<?php
if (!defined('ALPHA_BOOTSTRAP')) { http_response_code(403); exit('Forbidden'); }

/** Escape for HTML output. */
function e(?string $v): string {
    return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Build absolute URL from path. */
function url(string $path = '/'): string {
    if (str_starts_with($path, 'http')) return $path;
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/** Asset URL with cache-bust based on file mtime. */
function asset(string $path): string {
    $full = PUBLIC_PATH . '/' . ltrim($path, '/');
    $v = is_file($full) ? filemtime($full) : time();
    return '/' . ltrim($path, '/') . '?v=' . $v;
}

/** Generate URL-safe slug. */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text) ?: 'n-a';
}

/** Friendly date. */
function fmt_date($v, string $format = 'M j, Y'): string {
    if (!$v) return '';
    $ts = is_numeric($v) ? (int)$v : strtotime($v);
    return $ts ? date($format, $ts) : '';
}

/** Truncate to N words. */
function excerpt(?string $text, int $words = 30): string {
    $clean = trim(strip_tags($text ?? ''));
    $arr = preg_split('/\s+/', $clean);
    if (count($arr) <= $words) return $clean;
    return implode(' ', array_slice($arr, 0, $words)) . '…';
}

/** Read time estimate (200 wpm). */
function read_time(?string $body): int {
    $words = str_word_count(strip_tags($body ?? ''));
    return max(1, (int) ceil($words / 200));
}

/** Render a view with variables. */
function view(string $path, array $vars = []): void {
    extract($vars, EXTR_SKIP);
    $file = VIEWS_PATH . '/' . $path . '.php';
    if (!is_file($file)) {
        http_response_code(500);
        echo "View not found: $path";
        return;
    }
    require $file;
}

/** Render a partial. */
function partial(string $name, array $vars = []): void {
    view('partials/' . $name, $vars);
}

/** JSON response + exit. */
function json_out($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/** Redirect helper. */
function redirect(string $path, int $status = 302): void {
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)), true, $status);
    exit;
}

/** Get client IP (respects common proxy headers if behind Cloudflare/etc). */
function client_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = trim(explode(',', $_SERVER[$k])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

/** Flash message helpers. */
function flash_set(string $type, string $msg): void {
    $_SESSION['_flash'][$type][] = $msg;
}
function flash_get(): array {
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

/** Active nav helper. */
function is_active(string $path): bool {
    $current = '/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    $path    = '/' . trim($path, '/');
    if ($path === '/') return $current === '/';
    return str_starts_with($current, $path);
}

/** Rate-limit form submissions per IP. */
function rate_limit_ok(string $form_type): bool {
    $ip = client_ip();
    $cutoff = DB_DRIVER === 'sqlite' ? "datetime('now','-1 hour')" : "(NOW() - INTERVAL 1 HOUR)";
    db_exec("DELETE FROM form_rate_limits WHERE submitted_at < $cutoff");
    $row = db_one(
        "SELECT COUNT(*) AS c FROM form_rate_limits WHERE ip_address = ? AND form_type = ? AND submitted_at > $cutoff",
        [$ip, $form_type]
    );
    if (($row['c'] ?? 0) >= FORM_RATE_LIMIT) return false;
    db_exec("INSERT INTO form_rate_limits (ip_address, form_type) VALUES (?, ?)", [$ip, $form_type]);
    return true;
}

/** Render JSON-LD safely. */
function jsonld(array $data): string {
    return '<script type="application/ld+json">' .
           json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) .
           '</script>';
}
