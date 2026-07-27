<?php
$page_title = 'Blog Posts';
$rows = db_all("SELECT id,title,slug,category,status,published_at,updated_at FROM blog_posts ORDER BY COALESCE(published_at, updated_at) DESC");
ob_start(); ?>
<div class="page-header">
  <h1>Blog Posts</h1>
  <a class="btn btn-primary" href="/admin/blog/new">+ New Post</a>
</div>
<table>
  <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Published</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td><strong style="color:var(--text)"><?= e($r['title']) ?></strong><br><small style="color:var(--mute2)">/blog/<?= e($r['slug']) ?></small></td>
      <td><?= e($r['category']) ?></td>
      <td><span class="badge <?= $r['status']==='published'?'badge-success':'' ?>"><?= e($r['status']) ?></span></td>
      <td><?= fmt_date($r['published_at']) ?></td>
      <td class="tile-actions">
        <a href="/admin/blog/edit?id=<?= (int)$r['id'] ?>">Edit</a>
        <a href="/blog/<?= e($r['slug']) ?>" target="_blank">View</a>
        <a href="/admin/blog/delete?id=<?= (int)$r['id'] ?>" onclick="return confirm('Delete this post?')" style="color:var(--danger)">Delete</a>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="5" style="text-align:center;color:var(--mute2)">No posts. <a href="/admin/blog/new">Create one →</a></td></tr><?php endif; ?>
  </tbody>
</table>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../_layout.php';
