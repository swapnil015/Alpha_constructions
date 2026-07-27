<?php
$page_title = 'Inquiry';
$id = (int)($_GET['id'] ?? 0);
$r  = db_one("SELECT * FROM inquiries WHERE id = ?", [$id]);
if (!$r) { flash_set('error','Not found.'); redirect('/admin/inquiries'); }
if ($r['status'] === 'new') db_exec("UPDATE inquiries SET status='read' WHERE id = ?", [$id]);
ob_start(); ?>
<div class="page-header"><h1>Inquiry #<?= (int)$r['id'] ?></h1><a class="btn btn-ghost" href="/admin/inquiries">← Back</a></div>
<div class="card">
  <p><strong>From:</strong> <?= e($r['name']) ?> &lt;<a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a>&gt;</p>
  <p><strong>Phone:</strong> <?= e($r['phone']) ?></p>
  <p><strong>Subject:</strong> <?= e($r['subject']) ?></p>
  <p><strong>Received:</strong> <?= e($r['created_at']) ?> from <?= e($r['ip_address']) ?></p>
  <hr>
  <p><?= nl2br(e($r['message'])) ?></p>
  <hr>
  <a class="btn btn-primary" href="mailto:<?= e($r['email']) ?>?subject=Re: <?= rawurlencode($r['subject']) ?>">Reply via Email</a>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../_layout.php';
