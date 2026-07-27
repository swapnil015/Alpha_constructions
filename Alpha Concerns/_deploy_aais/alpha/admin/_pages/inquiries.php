<?php
$page_title = 'Inquiries';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrf_verify_or_die();
    $id = (int)($_POST['id'] ?? 0);
    $st = $_POST['action'];
    if ($id && in_array($st, ['read','replied','archived','new'], true)) {
        db_exec("UPDATE inquiries SET status = ? WHERE id = ?", [$st, $id]);
    }
    redirect('/admin/inquiries');
}

if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="inquiries-' . date('Ymd') . '.csv"');
    $out = fopen('php://output','w');
    fputcsv($out, ['ID','Name','Email','Phone','Subject','Message','Status','Received']);
    foreach (db_all("SELECT * FROM inquiries ORDER BY created_at DESC") as $r) {
        fputcsv($out, [$r['id'],$r['name'],$r['email'],$r['phone'],$r['subject'],$r['message'],$r['status'],$r['created_at']]);
    }
    exit;
}

$filter = $_GET['status'] ?? '';
$where  = $filter ? 'WHERE status = ?' : '';
$args   = $filter ? [$filter] : [];
$rows   = db_all("SELECT * FROM inquiries $where ORDER BY created_at DESC LIMIT 200", $args);
ob_start(); ?>
<div class="page-header">
  <h1>Inquiries</h1>
  <div>
    <select onchange="location='/admin/inquiries' + (this.value ? '?status=' + this.value : '')" style="max-width:180px; display:inline-block;">
      <option value="">All</option>
      <?php foreach (['new','read','replied','archived'] as $s): ?>
        <option value="<?= $s ?>" <?= $filter===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
    <a class="btn btn-ghost" href="/admin/inquiries?export=csv">Export CSV</a>
  </div>
</div>

<table>
  <thead><tr><th>Status</th><th>Name</th><th>Subject</th><th>Email / Phone</th><th>Message</th><th>Received</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
    <tr class="<?= $r['status']==='new'?'is-new':'' ?>">
      <td><span class="badge <?= $r['status']==='new'?'badge-new':'' ?>"><?= e($r['status']) ?></span></td>
      <td><strong style="color:var(--text)"><?= e($r['name']) ?></strong></td>
      <td><?= e($r['subject']) ?></td>
      <td><a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a><br><small><?= e($r['phone']) ?></small></td>
      <td style="max-width:340px;"><?= nl2br(e(excerpt($r['message'], 25))) ?></td>
      <td><?= fmt_date($r['created_at'],'M j · g:ia') ?></td>
      <td>
        <form method="post" style="display:inline;">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
          <select name="action" onchange="this.form.submit()">
            <option>Set status…</option>
            <option value="read">Mark Read</option>
            <option value="replied">Mark Replied</option>
            <option value="archived">Archive</option>
            <option value="new">Mark New</option>
          </select>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="7" style="text-align:center;color:var(--mute2)">No inquiries.</td></tr><?php endif; ?>
  </tbody>
</table>
<?php
$content = ob_get_clean();
require __DIR__ . '/../_layout.php';
