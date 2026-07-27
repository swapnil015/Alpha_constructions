<?php
if (!defined('ALPHA_BOOTSTRAP')) { http_response_code(403); exit('Forbidden'); }

function auth_user(): ?array {
    if (empty($_SESSION['admin_user_id'])) return null;
    static $u = null;
    if ($u !== null) return $u;
    $u = db_one('SELECT id, name, email, role, is_active FROM admin_users WHERE id = ?', [$_SESSION['admin_user_id']]);
    if (!$u || !$u['is_active']) {
        auth_logout();
        return null;
    }
    return $u;
}

function auth_require(?string $role = null): array {
    $u = auth_user();
    if (!$u) {
        $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'] ?? '/admin/';
        redirect('/admin/login.php');
    }
    if ($role && $u['role'] !== $role) {
        http_response_code(403);
        exit('Forbidden — insufficient permissions.');
    }
    return $u;
}

/**
 * Attempt login. Returns user array on success, error string on failure.
 */
function auth_attempt(string $email, string $password) {
    $u = db_one('SELECT * FROM admin_users WHERE email = ?', [$email]);
    if (!$u || !$u['is_active']) {
        return 'Invalid credentials.';
    }
    if (!empty($u['locked_until']) && strtotime($u['locked_until']) > time()) {
        return 'Account temporarily locked. Try again later.';
    }
    if (!password_verify($password, $u['password_hash'])) {
        $attempts = (int)$u['failed_attempts'] + 1;
        $lock = $attempts >= LOGIN_MAX_ATTEMPTS
            ? date('Y-m-d H:i:s', time() + LOGIN_LOCKOUT_MINUTES * 60)
            : null;
        db_exec('UPDATE admin_users SET failed_attempts = ?, locked_until = ? WHERE id = ?',
                [$attempts, $lock, $u['id']]);
        return 'Invalid credentials.';
    }
    // Success.
    db_exec('UPDATE admin_users SET failed_attempts = 0, locked_until = NULL, last_login = CURRENT_TIMESTAMP WHERE id = ?', [$u['id']]);
    session_regenerate_id(true);
    $_SESSION['admin_user_id'] = (int)$u['id'];
    auth_log($u['id'], 'login', 'admin_user', (int)$u['id'], 'Successful login');
    return $u;
}

function auth_logout(): void {
    if (!empty($_SESSION['admin_user_id'])) {
        auth_log($_SESSION['admin_user_id'], 'logout', 'admin_user', $_SESSION['admin_user_id'], 'Logout');
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function auth_log(?int $uid, string $action, string $entityType = '', int $entityId = 0, string $desc = ''): void {
    try {
        db_exec(
            'INSERT INTO admin_activity_log (user_id, action, entity_type, entity_id, description, ip_address) VALUES (?,?,?,?,?,?)',
            [$uid, $action, $entityType, $entityId, $desc, client_ip()]
        );
    } catch (Throwable $e) { /* tolerate */ }
}
