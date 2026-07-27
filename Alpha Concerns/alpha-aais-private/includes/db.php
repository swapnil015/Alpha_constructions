<?php
if (!defined('ALPHA_BOOTSTRAP')) { http_response_code(403); exit('Forbidden'); }

/**
 * PDO database singleton.
 */
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            if (DB_DRIVER === 'sqlite') {
                $needsInit = !is_file(SQLITE_PATH);
                if ($needsInit && !is_dir(dirname(SQLITE_PATH))) @mkdir(dirname(SQLITE_PATH), 0755, true);
                $pdo = new PDO('sqlite:' . SQLITE_PATH, null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                $pdo->exec('PRAGMA foreign_keys = ON');
                if ($needsInit) {
                    $sql = file_get_contents(ROOT_PATH . '/database/schema_sqlite.sql');
                    if ($sql) $pdo->exec($sql);
                }
                return $pdo;
            }
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            if (APP_DEBUG) {
                exit('DB Error: ' . htmlspecialchars($e->getMessage()));
            }
            exit('Database temporarily unavailable. Please try again shortly.');
        }
    }
    return $pdo;
}

/** Convenience query helpers. */
function db_one(string $sql, array $params = []): ?array {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function db_all(string $sql, array $params = []): array {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function db_exec(string $sql, array $params = []): int {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

function db_insert(string $sql, array $params = []): int {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int) db()->lastInsertId();
}

/** Get a single setting value. */
function setting(string $key, ?string $default = null): ?string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (db_all('SELECT setting_key, setting_value FROM site_settings') as $r) {
                $cache[$r['setting_key']] = $r['setting_value'];
            }
        } catch (Throwable $e) { /* tolerate before install */ }
    }
    return $cache[$key] ?? $default;
}
