<?php
$page_title = 'Dashboard';
$kpis = [
    'Projects'    => db_one("SELECT COUNT(*) c FROM projects")['c'] ?? 0,
    'Blog Posts'  => db_one("SELECT COUNT(*) c FROM blog_posts")['c'] ?? 0,
    'Inquiries (new)' => db_one("SELECT COUNT(*) c FROM inquiries WHERE status='new'")['c'] ?? 0,
    'Open Roles'  => db_one("SELECT COUNT(*) c FROM job_listings WHERE is_active=1")['c'] ?? 0,
];
$recent = db_all("SELECT id,name,email,subject,status,created_at FROM inquiries ORDER BY created_at DESC LIMIT 10");

ob_start(); ?>
<div class="page-header">
  <h1>Dashboard</h1>
  <div>
    <a class="btn btn-primary" href="/admin/projects/new">+ New Project</a>
    <a class="btn btn-ghost" href="/admin/blog/new">+ New Post</a>
  </div>
</div>

<div class="kpis">
  <?php foreach ($kpis as $label => $val): ?>
    <div class="card">
      <div class="card-title"><?= e($label) ?></div>
      <div class="card-num"><?= (int)$val ?></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
    <h2 style="margin:0;font-family:'Cormorant Garamond',serif;font-weight:400;font-size:1.4rem;">Recent inquiries</h2>
    <a href="/admin/inquiries" style="font-size:.75rem;letter-spacing:.1em;text-transform:uppercase;">View all →</a>
  </div>
  <table>
    <thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Status</th><th>Received</th><th></th></tr></thead>
    <tbody>
      <?php if (!$recent): ?>
        <tr><td colspan="6" style="text-align:center;color:var(--mute2)">No inquiries yet.</td></tr>
      <?php endif; ?>
      <?php foreach ($recent as $r): ?>
      <tr class="<?= $r['status']==='new'?'is-new':'' ?>">
        <td><?= e($r['name']) ?></td>
        <td><?= e($r['email']) ?></td>
        <td><?= e($r['subject']) ?></td>
        <td><span class="badge badge-<?= $r['status']==='new'?'new':'' ?>"><?= e($r['status']) ?></span></td>
        <td><?= fmt_date($r['created_at'],'M j · g:ia') ?></td>
        <td><a href="/admin/inquiries/view?id=<?= (int)$r['id'] ?>">View</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../_layout.php';
