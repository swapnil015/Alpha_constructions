<?php
$page_title = 'Careers';
$jobs = db_all("SELECT j.*, (SELECT COUNT(*) FROM job_applications a WHERE a.job_id = j.id) AS app_count FROM job_listings j ORDER BY is_active DESC, created_at DESC");
ob_start(); ?>
<div class="page-header">
  <h1>Career Listings</h1>
  <a class="btn btn-primary" href="/admin/careers/edit">+ New Listing</a>
</div>
<table>
  <thead><tr><th>Title</th><th>Department</th><th>Type</th><th>Active</th><th>Applications</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($jobs as $j): ?>
    <tr>
      <td><strong style="color:var(--text)"><?= e($j['title']) ?></strong></td>
      <td><?= e($j['department']) ?></td>
      <td><?= e($j['employment_type']) ?></td>
      <td><?= $j['is_active']?'<span class="badge badge-success">Active</span>':'<span class="badge">Closed</span>' ?></td>
      <td><a href="/admin/careers/applications?job=<?= (int)$j['id'] ?>"><?= (int)$j['app_count'] ?> applications →</a></td>
      <td><a href="/admin/careers/edit?id=<?= (int)$j['id'] ?>">Edit</a></td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$jobs): ?><tr><td colspan="6" style="text-align:center;color:var(--mute2)">No listings.</td></tr><?php endif; ?>
  </tbody>
</table>
<?php
$content = ob_get_clean();
require __DIR__ . '/../_layout.php';
