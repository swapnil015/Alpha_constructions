<?php
if (!defined('ALPHA_BOOTSTRAP')) require __DIR__ . '/../../includes/bootstrap.php';
if (auth_user()) redirect('/admin/dashboard');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $res   = auth_attempt($email, $pass);
    if (is_array($res)) {
        $to = $_SESSION['login_redirect'] ?? '/admin/dashboard';
        unset($_SESSION['login_redirect']);
        redirect($to);
    }
    $error = $res;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in — <?= e(SITE_NAME) ?> Admin</title>
<style>
body{margin:0;min-height:100vh;display:grid;place-items:center;background:#0D1B2A;color:#F4F1EC;font-family:'DM Sans',system-ui,sans-serif}
.box{width:100%;max-width:380px;padding:2.5rem;background:#132233;border:1px solid rgba(248,240,220,.08);border-radius:4px}
.brand{font-family:'Cormorant Garamond',serif;font-size:1.75rem;text-align:center;margin-bottom:.25rem}
.brand .accent{color:#C8922A}
.tag{font-size:.65rem;letter-spacing:.25em;text-transform:uppercase;color:#5C6E7E;text-align:center;margin-bottom:2rem}
.field{margin-bottom:1.1rem}
label{display:block;font-size:.7rem;letter-spacing:.15em;text-transform:uppercase;color:#5C6E7E;margin-bottom:.4rem}
input{width:100%;padding:.75rem .9rem;background:#0D1B2A;border:1px solid rgba(248,240,220,.08);color:#F4F1EC;font-size:.95rem;border-radius:3px;font-family:inherit}
input:focus{outline:none;border-color:#C8922A}
button{width:100%;padding:.85rem;background:#C8922A;color:#000;border:0;font-size:.8rem;letter-spacing:.15em;text-transform:uppercase;font-weight:500;cursor:pointer;border-radius:3px}
button:hover{background:#E8B860}
.err{padding:.75rem 1rem;background:rgba(232,131,95,.1);border:1px solid #E8835F;color:#E8835F;font-size:.85rem;margin-bottom:1.1rem;border-radius:3px}
</style>
</head>
<body>
  <div class="box">
    <div class="brand">Alpha<span class="accent">·</span>Concern</div>
    <div class="tag">Admin Console</div>
    <?php if ($error): ?><div class="err"><?= e($error) ?></div><?php endif; ?>
    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      <div class="field"><label>Email</label><input type="email" name="email" required autofocus></div>
      <div class="field"><label>Password</label><input type="password" name="password" required></div>
      <button type="submit">Sign in</button>
    </form>
  </div>
</body>
</html>
