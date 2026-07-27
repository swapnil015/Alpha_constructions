<?php
$page_title = 'Edit Listing';
$id = (int)($_GET['id'] ?? 0);
$j  = $id ? db_one("SELECT * FROM job_listings WHERE id = ?", [$id]) : null;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify_or_die();
    $title=trim($_POST['title']??''); $dept=trim($_POST['department']??''); $type=$_POST['employment_type']??'Full-time';
    $loc=trim($_POST['location']??'Kathmandu, Nepal'); $desc=$_POST['description']??''; $req=$_POST['requirements']??'';
    $deadline=$_POST['deadline']?:null; $active=isset($_POST['is_active'])?1:0;
    if ($title==='') { flash_set('error','Title required.'); redirect($_SERVER['REQUEST_URI']); }
    if ($id) {
        db_exec("UPDATE job_listings SET title=?,department=?,employment_type=?,location=?,description=?,requirements=?,deadline=?,is_active=? WHERE id=?",
                [$title,$dept,$type,$loc,$desc,$req,$deadline,$active,$id]);
        flash_set('success','Listing updated.');
    } else {
        $id = db_insert("INSERT INTO job_listings (title,department,employment_type,location,description,requirements,deadline,is_active) VALUES (?,?,?,?,?,?,?,?)",
                       [$title,$dept,$type,$loc,$desc,$req,$deadline,$active]);
        flash_set('success','Listing created.');
    }
    redirect('/admin/careers');
}

ob_start(); ?>
<div class="page-header"><h1><?= $id?'Edit Listing':'New Listing' ?></h1><a class="btn btn-ghost" href="/admin/careers">← Back</a></div>
<form method="post">
  <?= csrf_field() ?>
  <div class="card">
    <div class="row">
      <div class="field"><label>Title *</label><input name="title" required value="<?= e($j['title'] ?? '') ?>"></div>
      <div class="field"><label>Department</label><input name="department" value="<?= e($j['department'] ?? '') ?>"></div>
    </div>
    <div class="row">
      <div class="field"><label>Type</label>
        <select name="employment_type">
          <?php foreach (['Full-time','Part-time','Contract','Internship'] as $t): ?>
            <option <?= ($j['employment_type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Location</label><input name="location" value="<?= e($j['location'] ?? 'Kathmandu, Nepal') ?>"></div>
    </div>
    <div class="row">
      <div class="field"><label>Deadline</label><input type="date" name="deadline" value="<?= e($j['deadline'] ?? '') ?>"></div>
      <div class="field" style="display:flex;align-items:end;">
        <label style="display:flex;gap:.5rem;align-items:center;text-transform:none;letter-spacing:0;font-size:.9rem;color:var(--text);">
          <input type="checkbox" name="is_active" <?= !empty($j['is_active'])||!$id?'checked':'' ?>> Active
        </label>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="field"><label>Description (HTML)</label><textarea name="description" rows="8"><?= e($j['description'] ?? '') ?></textarea></div>
    <div class="field"><label>Requirements (HTML)</label><textarea name="requirements" rows="6"><?= e($j['requirements'] ?? '') ?></textarea></div>
  </div>
  <div class="actions"><button class="btn btn-primary" type="submit"><?= $id?'Save':'Create' ?></button></div>
</form>
<?php $content = ob_get_clean(); require __DIR__ . '/../_layout.php';
