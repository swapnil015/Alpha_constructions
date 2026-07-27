<?php
$page_title = 'Media Library';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify_or_die();
    if (!empty($_FILES['files']['name'][0])) {
        foreach ($_FILES['files']['name'] as $k => $name) {
            if (!$name) continue;
            $f = ['name'=>$name,'type'=>$_FILES['files']['type'][$k],'tmp_name'=>$_FILES['files']['tmp_name'][$k],'error'=>$_FILES['files']['error'][$k],'size'=>$_FILES['files']['size'][$k]];
            $u = upload_image($f, 'misc');
            if ($u) db_exec("INSERT INTO media_files (filename, original_name, file_path, file_type, file_size, uploaded_by) VALUES (?,?,?,?,?,?)",
                            [basename($u), $name, $u, $_FILES['files']['type'][$k], $_FILES['files']['size'][$k], auth_user()['id']]);
        }
        flash_set('success','Files uploaded.');
    }
    redirect('/admin/media');
}

$rows = db_all("SELECT * FROM media_files ORDER BY created_at DESC LIMIT 200");
ob_start(); ?>
<div class="page-header"><h1>Media Library</h1></div>

<div class="card">
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <label>Upload images (multiple)</label>
    <input type="file" name="files[]" multiple accept="image/*" required>
    <div class="actions"><button class="btn btn-primary" type="submit">Upload</button></div>
  </form>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.65rem;">
  <?php foreach ($rows as $r): ?>
    <div style="background:var(--surface);border:1px solid var(--border);">
      <div style="aspect-ratio:1;background:#000 url('<?= e($r['file_path']) ?>') center/cover;"></div>
      <div style="padding:.5rem;font-size:.7rem;">
        <div style="color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($r['original_name']) ?></div>
        <input readonly value="<?= e($r['file_path']) ?>" style="margin-top:.4rem;font-size:.7rem;padding:.3rem;" onclick="this.select()">
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../_layout.php';
