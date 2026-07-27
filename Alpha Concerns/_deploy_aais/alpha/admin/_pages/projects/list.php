<?php
$page_title = 'Projects';
$rows = db_all("SELECT id,title,slug,type,status,is_published,is_featured,sort_order,updated_at FROM projects ORDER BY sort_order, id");
ob_start(); ?>
<div class="page-header">
  <h1>Projects</h1>
  <a class="btn btn-primary" href="/admin/projects/new">+ New Project</a>
</div>

<table>
  <thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Published</th><th>Featured</th><th>Updated</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td><strong style="color:var(--text)"><?= e($r['title']) ?></strong><br><small style="color:var(--mute2)">/projects/<?= e($r['slug']) ?></small></td>
      <td><?= e($r['type']) ?></td>
      <td><span class="badge"><?= e($r['status']) ?></span></td>
      <td><?= $r['is_published'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge">No</span>' ?></td>
      <td><?= $r['is_featured'] ? '★' : '—' ?></td>
      <td><?= fmt_date($r['updated_at'],'M j, Y') ?></td>
      <td class="tile-actions">
        <a href="/admin/projects/edit?id=<?= (int)$r['id'] ?>">Edit</a>
        <a href="/projects/<?= e($r['slug']) ?>" target="_blank">View</a>
        <a href="/admin/projects/delete?id=<?= (int)$r['id'] ?>" onclick="return confirm('Delete this project?')" style="color:var(--danger)">Delete</a>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="7" style="text-align:center;color:var(--mute2)">No projects yet. <a href="/admin/projects/new">Create one →</a></td></tr><?php endif; ?>
  </tbody>
</table>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../_layout.php';
