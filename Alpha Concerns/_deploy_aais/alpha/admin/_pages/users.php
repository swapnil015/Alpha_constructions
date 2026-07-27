<?php
$me = auth_require('superadmin');
$page_title = 'Admin Users';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify_or_die();
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $pass=$_POST['password']??''; $role=$_POST['role']??'editor';
        if ($name && filter_var($email,FILTER_VALIDATE_EMAIL) && strlen($pass) >= 10) {
            try {
                db_exec("INSERT INTO admin_users (name,email,password_hash,role) VALUES (?,?,?,?)",
                        [$name,$email,password_hash($pass,PASSWORD_BCRYPT,['cost'=>PASSWORD_BCRYPT_COST]),$role]);
                flash_set('success','User created.');
            } catch (Throwable $e) { flash_set('error','Email already exists.'); }
        } else { flash_set('error','Invalid input. Password must be ≥10 chars.'); }
    } elseif ($action === 'reset') {
        $id=(int)($_POST['id']??0); $pass=$_POST['password']??'';
        if ($id && strlen($pass) >= 10) {
            db_exec("UPDATE admin_users SET password_hash=?, failed_attempts=0, locked_until=NULL WHERE id=?",
                    [password_hash($pass,PASSWORD_BCRYPT,['cost'=>PASSWORD_BCRYPT_COST]),$id]);
            flash_set('success','Password reset.');
        }
    } elseif ($action === 'toggle') {
        $id=(int)($_POST['id']??0);
        if ($id && $id !== (int)$me['id']) db_exec("UPDATE admin_users SET is_active = 1 - is_active WHERE id=?", [$id]);
    }
    redirect('/admin/users');
}

$users = db_all("SELECT id,name,email,role,is_active,last_login,created_at FROM admin_users ORDER BY id");
ob_start(); ?>
<div class="page-header"><h1>Admin Users</h1></div>

<div class="card">
  <h3 style="margin-top:0;font-family:'Cormorant Garamond',serif;font-weight:400;">Add user</h3>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="create">
    <div class="row">
      <div class="field"><label>Name</label><input name="name" required></div>
      <div class="field"><label>Email</label><input type="email" name="email" required></div>
    </div>
    <div class="row">
      <div class="field"><label>Temporary Password (≥10 chars)</label><input type="password" name="password" required minlength="10"></div>
      <div class="field"><label>Role</label><select name="role"><option value="editor">Editor</option><option value="superadmin">Superadmin</option></select></div>
    </div>
    <div class="actions"><button class="btn btn-primary" type="submit">Create User</button></div>
  </form>
</div>

<table>
  <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th>Last login</th><th>Actions</th></tr></thead>
  <tbody>
    <?php foreach ($users as $u): ?>
    <tr>
      <td><strong style="color:var(--text)"><?= e($u['name']) ?></strong></td>
      <td><?= e($u['email']) ?></td>
      <td><span class="badge"><?= e($u['role']) ?></span></td>
      <td><?= $u['is_active']?'Yes':'No' ?></td>
      <td><?= $u['last_login'] ? fmt_date($u['last_login'],'M j · g:ia') : '—' ?></td>
      <td>
        <details>
          <summary style="cursor:pointer;color:var(--accent2)">Reset password</summary>
          <form method="post" style="margin-top:.5rem;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="reset">
            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <input type="password" name="password" placeholder="New password" minlength="10" required style="width:200px;display:inline-block;">
            <button class="btn btn-ghost" type="submit">Reset</button>
          </form>
        </details>
        <?php if ((int)$u['id'] !== (int)$me['id']): ?>
        <form method="post" style="display:inline;margin-top:.5rem;">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="toggle">
          <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
          <button class="btn btn-danger" type="submit"><?= $u['is_active']?'Deactivate':'Activate' ?></button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php $content = ob_get_clean(); require __DIR__ . '/../_layout.php';
