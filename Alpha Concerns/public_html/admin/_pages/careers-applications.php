<?php
$page_title = 'Applications';
$jobId = (int)($_GET['job'] ?? 0);

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify_or_die();
    $id = (int)($_POST['id'] ?? 0);
    $st = $_POST['status'] ?? '';
    if ($id && in_array($st, ['new','reviewed','shortlisted','rejected'], true)) {
        db_exec("UPDATE job_applications SET status = ? WHERE id = ?", [$st, $id]);
    }
    redirect('/admin/careers/applications' . ($jobId ? '?job=' . $jobId : ''));
}

$where = $jobId ? 'WHERE a.job_id = ?' : '';
$args  = $jobId ? [$jobId] : [];
$rows  = db_all("SELECT a.*, j.title AS job_title FROM job_applications a LEFT JOIN job_listings j ON j.id = a.job_id $where ORDER BY a.created_at DESC", $args);
ob_start(); ?>
<div class="page-header"><h1>Applications</h1><a class="btn btn-ghost" href="/admin/careers">← Back to listings</a></div>
<table>
  <thead><tr><th>Position</th><th>Applicant</th><th>Email/Phone</th><th>CV</th><th>Status</th><th>Received</th></tr></thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= e($r['job_title']) ?></td>
      <td><strong style="color:var(--text)"><?= e($r['applicant_name']) ?></strong></td>
      <td><a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a><br><small><?= e($r['phone']) ?></small></td>
      <td><a href="/admin/careers/cv?id=<?= (int)$r['id'] ?>" target="_blank">Download</a></td>
      <td>
        <form method="post" style="display:inline;">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
          <select name="status" onchange="this.form.submit()">
            <?php foreach (['new','reviewed','shortlisted','rejected'] as $s): ?>
              <option value="<?= $s ?>" <?= $r['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </td>
      <td><?= fmt_date($r['created_at'],'M j · g:ia') ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="6" style="text-align:center;color:var(--mute2)">No applications yet.</td></tr><?php endif; ?>
  </tbody>
</table>
<?php $content = ob_get_clean(); require __DIR__ . '/../_layout.php';
