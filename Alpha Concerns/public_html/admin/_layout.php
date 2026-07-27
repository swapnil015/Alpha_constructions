<?php
/**
 * Shared admin layout. Pages should:
 *   $page_title = 'X';
 *   ob_start();
 *   ... markup ...
 *   $content = ob_get_clean();
 *   require __DIR__ . '/../_layout.php';   (path varies)
 */
$u = auth_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title ?? 'Admin') ?> — <?= e(SITE_NAME) ?> Admin</title>
<style>
:root{--bg:#0D1B2A;--surface:#132233;--surface2:#182B40;--border:rgba(248,240,220,0.08);--text:#F4F1EC;--muted:#9BA8B4;--mute2:#5C6E7E;--accent:#C8922A;--accent2:#E8B860;--danger:#E8835F;--success:#7FBF7F;}
*{box-sizing:border-box}
body{margin:0;font-family:'DM Sans',system-ui,sans-serif;background:var(--bg);color:var(--text);font-size:14px}
a{color:var(--accent2);text-decoration:none}
a:hover{color:var(--accent)}
.layout{display:grid;grid-template-columns:240px 1fr;min-height:100vh}
@media (max-width:900px){.layout{grid-template-columns:1fr}.sidebar{display:none}}
.sidebar{background:var(--surface);border-right:1px solid var(--border);padding:1.5rem 1rem;position:sticky;top:0;height:100vh;overflow-y:auto}
.brand{font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:var(--text);margin-bottom:.25rem}
.brand .accent{color:var(--accent)}
.tag{font-size:.6rem;color:var(--mute2);letter-spacing:.2em;text-transform:uppercase;margin-bottom:1.5rem}
.nav-group{margin-bottom:1.25rem}
.nav-group__title{font-size:.65rem;letter-spacing:.2em;text-transform:uppercase;color:var(--mute2);padding:0 .75rem;margin-bottom:.5rem}
.nav-link{display:block;padding:.65rem .75rem;color:var(--muted);font-size:.85rem;border-radius:3px;transition:all .2s}
.nav-link:hover{background:var(--surface2);color:var(--text)}
.nav-link.is-active{background:rgba(200,146,42,.12);color:var(--text);border-left:2px solid var(--accent)}
.main{padding:0}
.topbar{display:flex;justify-content:space-between;align-items:center;padding:1rem 2rem;background:var(--surface);border-bottom:1px solid var(--border)}
.topbar .user{font-size:.85rem;color:var(--muted)}
.content{padding:2rem}
@media (max-width:600px){.content{padding:1.25rem}}
.page-header{display:flex;justify-content:space-between;align-items:end;margin-bottom:2rem;gap:1rem;flex-wrap:wrap}
.page-header h1{font-family:'Cormorant Garamond',serif;font-weight:400;font-size:2rem;margin:0}
.flash{padding:.85rem 1rem;border-radius:3px;margin-bottom:1rem;border:1px solid}
.flash-success{background:rgba(127,191,127,.1);border-color:var(--success);color:var(--success)}
.flash-error{background:rgba(232,131,95,.1);border-color:var(--danger);color:var(--danger)}
.btn{display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.15rem;font-size:.8rem;letter-spacing:.1em;text-transform:uppercase;font-weight:500;border:1px solid transparent;border-radius:3px;cursor:pointer;transition:all .2s;background:none;color:inherit}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--accent2)}
.btn-ghost{border-color:var(--border);color:var(--text)}
.btn-ghost:hover{border-color:var(--accent)}
.btn-danger{background:transparent;color:var(--danger);border-color:var(--danger)}
.btn-danger:hover{background:var(--danger);color:#000}
.card{background:var(--surface);border:1px solid var(--border);border-radius:3px;padding:1.5rem;margin-bottom:1.25rem}
.card-title{font-size:.7rem;letter-spacing:.2em;text-transform:uppercase;color:var(--mute2);margin-bottom:.75rem}
.card-num{font-family:'Cormorant Garamond',serif;font-size:2.5rem;color:var(--accent);line-height:1}
.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:2rem}
table{width:100%;border-collapse:collapse;background:var(--surface);border:1px solid var(--border)}
th,td{padding:.85rem 1rem;text-align:left;border-bottom:1px solid var(--border);font-size:.85rem}
th{background:var(--surface2);color:var(--mute2);font-size:.7rem;letter-spacing:.15em;text-transform:uppercase;font-weight:500}
tr:last-child td{border-bottom:0}
tr.is-new td{background:rgba(200,146,42,.04)}
.badge{display:inline-block;padding:.2rem .55rem;font-size:.65rem;letter-spacing:.15em;text-transform:uppercase;border-radius:2px;background:var(--surface2);color:var(--muted)}
.badge-new{background:var(--accent);color:#000}
.badge-success{background:rgba(127,191,127,.2);color:var(--success)}
.badge-warn{background:rgba(232,131,95,.2);color:var(--danger)}
form .row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
@media (max-width:700px){form .row{grid-template-columns:1fr}}
label{display:block;font-size:.7rem;letter-spacing:.15em;text-transform:uppercase;color:var(--mute2);margin-bottom:.4rem}
input[type=text],input[type=email],input[type=tel],input[type=password],input[type=number],input[type=url],input[type=date],input[type=datetime-local],select,textarea{width:100%;padding:.7rem .85rem;background:var(--bg);border:1px solid var(--border);color:var(--text);font-family:inherit;font-size:.9rem;border-radius:3px}
textarea{min-height:120px;resize:vertical}
input:focus,select:focus,textarea:focus{outline:none;border-color:var(--accent)}
.field{margin-bottom:1.1rem}
.help{color:var(--mute2);font-size:.75rem;margin-top:.35rem}
.actions{display:flex;gap:.5rem;justify-content:flex-end;margin-top:1.5rem}
.tile-actions a{margin-right:.5rem;font-size:.75rem;letter-spacing:.1em;text-transform:uppercase}
hr{border:0;border-top:1px solid var(--border);margin:1.5rem 0}
</style>
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <div class="brand">Alpha<span class="accent">·</span>Concern</div>
    <div class="tag">Admin Console</div>

    <div class="nav-group">
      <div class="nav-group__title">Overview</div>
      <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'],'/admin/dashboard')?'is-active':''?>" href="/admin/dashboard">Dashboard</a>
    </div>
    <div class="nav-group">
      <div class="nav-group__title">Content</div>
      <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'],'/admin/homepage')?'is-active':''?>" href="/admin/homepage">Homepage</a>
      <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'],'/admin/projects')?'is-active':''?>" href="/admin/projects">Projects</a>
      <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'],'/admin/services')?'is-active':''?>" href="/admin/services">Services</a>
      <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'],'/admin/team')?'is-active':''?>" href="/admin/team">Team</a>
      <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'],'/admin/blog')?'is-active':''?>" href="/admin/blog">Blog Posts</a>
      <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'],'/admin/media')?'is-active':''?>" href="/admin/media">Media Library</a>
    </div>
    <div class="nav-group">
      <div class="nav-group__title">Engagement</div>
      <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'],'/admin/inquiries')?'is-active':''?>" href="/admin/inquiries">Inquiries</a>
      <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'],'/admin/careers')?'is-active':''?>" href="/admin/careers">Careers</a>
    </div>
    <div class="nav-group">
      <div class="nav-group__title">System</div>
      <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'],'/admin/settings')?'is-active':''?>" href="/admin/settings">Site Settings</a>
      <?php if ($u && $u['role']==='superadmin'): ?>
      <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'],'/admin/users')?'is-active':''?>" href="/admin/users">Users</a>
      <?php endif; ?>
      <a class="nav-link" href="/" target="_blank">View Site ↗</a>
    </div>
  </aside>

  <div class="main">
    <div class="topbar">
      <div style="font-size:.85rem;color:var(--muted);">Signed in as <strong style="color:var(--text)"><?= e($u['name'] ?? '') ?></strong> · <?= e($u['role'] ?? '') ?></div>
      <div><a class="btn btn-ghost" href="/admin/logout">Logout</a></div>
    </div>
    <div class="content">
      <?php foreach (flash_get() as $type => $msgs) foreach ($msgs as $m): ?>
        <div class="flash flash-<?= e($type) ?>"><?= e($m) ?></div>
      <?php endforeach; ?>
      <?= $content ?? '' ?>
    </div>
  </div>
</div>
</body>
</html>
