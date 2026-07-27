<?php
/**
 * One-time setup helper. DELETE THIS FILE after use.
 *
 * Use it to:
 *   1. Verify database connection.
 *   2. Reset the seeded admin password to a known value (the seeded hash in
 *      schema.sql is a placeholder and may not match a known password).
 */
require __DIR__ . '/../includes/bootstrap.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 10) {
        $msg = 'Email must be valid and password ≥10 chars.';
    } else {
        $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => PASSWORD_BCRYPT_COST]);
        $existing = db_one('SELECT id FROM admin_users WHERE email = ?', [$email]);
        if ($existing) {
            db_exec('UPDATE admin_users SET password_hash=?, failed_attempts=0, locked_until=NULL, is_active=1 WHERE id=?',
                    [$hash, $existing['id']]);
        } else {
            db_exec('INSERT INTO admin_users (name,email,password_hash,role) VALUES (?,?,?,?)',
                    ['Super Admin', $email, $hash, 'superadmin']);
        }
        $msg = '✓ Admin set. <a href="/admin/login">Sign in →</a> — then DELETE setup.php.';
    }
}

try { db()->query('SELECT 1'); $dbOk = true; } catch (Throwable $e) { $dbOk = false; $dbErr = $e->getMessage(); }
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Setup</title>
<style>body{font-family:system-ui;max-width:560px;margin:4rem auto;padding:2rem;background:#0D1B2A;color:#F4F1EC}
input{width:100%;padding:.7rem;background:#132233;border:1px solid #333;color:#F4F1EC;margin:.25rem 0 1rem;border-radius:3px}
button{padding:.75rem 1.5rem;background:#C8922A;color:#000;border:0;font-weight:bold;border-radius:3px;cursor:pointer}
.ok{color:#7FBF7F}.err{color:#E8835F}.warn{background:#3a2a1a;padding:1rem;border-left:3px solid #C8922A;margin:1rem 0}</style>
</head><body>
<h1>Alpha Concern · Setup</h1>

<p><strong>Database:</strong> <?= $dbOk ? '<span class="ok">connected</span>' : '<span class="err">FAILED — ' . e($dbErr ?? '') . '</span>' ?></p>

<?php if ($dbOk): ?>
  <div class="warn"><strong>⚠ Delete this file after use.</strong> It allows password reset without authentication.</div>

  <?php if ($msg): ?><p><?= $msg ?></p><?php endif; ?>

  <h2>Set/reset admin password</h2>
  <form method="post">
    <label>Admin email</label>
    <input type="email" name="email" value="admin@alphaconcern.com" required>
    <label>New password (min 10 chars)</label>
    <input type="password" name="password" minlength="10" required>
    <button type="submit">Set Password</button>
  </form>
<?php else: ?>
  <p>Edit <code>/includes/config.php</code> and set <code>DB_NAME</code>, <code>DB_USER</code>, <code>DB_PASS</code>. Then make sure you imported <code>database/schema.sql</code> via phpMyAdmin.</p>
<?php endif; ?>
</body></html>
