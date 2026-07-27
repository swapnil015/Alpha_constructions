<?php
$page_title = 'Edit Project';
$id = (int)($_GET['id'] ?? 0);
$p  = $id ? db_one("SELECT * FROM projects WHERE id = ?", [$id]) : null;
if ($id && !$p) { flash_set('error','Project not found.'); redirect('/admin/projects'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();

    $title = trim($_POST['title'] ?? '');
    $slug  = slugify($_POST['slug'] ?? $title);
    $type  = $_POST['type'] ?? 'Residential';
    $status= $_POST['status'] ?? 'Ongoing';
    $loc   = trim($_POST['location'] ?? '');
    $short = trim($_POST['short_description'] ?? '');
    $full  = $_POST['full_description'] ?? '';
    $heroExisting = $_POST['hero_existing'] ?? '';
    $isPub = isset($_POST['is_published']) ? 1 : 0;
    $isFeat= isset($_POST['is_featured']) ? 1 : 0;
    $order = (int)($_POST['sort_order'] ?? 0);

    if ($title === '') { flash_set('error','Title is required.'); redirect($_SERVER['REQUEST_URI']); }

    // Specs JSON from key/value rows
    $specs = [];
    foreach (($_POST['spec_key'] ?? []) as $i => $k) {
        $k = trim($k); $v = trim($_POST['spec_val'][$i] ?? '');
        if ($k !== '' && $v !== '') $specs[$k] = $v;
    }
    $amenities = array_values(array_filter(array_map('trim', explode("\n", $_POST['amenities'] ?? ''))));

    // Hero image upload
    $hero = $heroExisting;
    if (!empty($_FILES['hero']['name'])) {
        $up = upload_image($_FILES['hero'], 'projects/' . $slug . '/hero', $slug . '-hero');
        if ($up) $hero = $up;
    }

    $seoTitle = trim($_POST['seo_title'] ?? '');
    $seoDesc  = trim($_POST['seo_description'] ?? '');

    if ($id) {
        db_exec("UPDATE projects SET title=?,slug=?,location=?,type=?,status=?,short_description=?,full_description=?,hero_image=?,key_specs=?,amenities=?,seo_title=?,seo_description=?,is_published=?,is_featured=?,sort_order=? WHERE id=?",
            [$title,$slug,$loc,$type,$status,$short,$full,$hero,json_encode($specs),json_encode($amenities),$seoTitle,$seoDesc,$isPub,$isFeat,$order,$id]);
        auth_log(auth_user()['id'],'update','project',$id,$title);
        flash_set('success','Project updated.');
    } else {
        $id = db_insert("INSERT INTO projects (title,slug,location,type,status,short_description,full_description,hero_image,key_specs,amenities,seo_title,seo_description,is_published,is_featured,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [$title,$slug,$loc,$type,$status,$short,$full,$hero,json_encode($specs),json_encode($amenities),$seoTitle,$seoDesc,$isPub,$isFeat,$order]);
        auth_log(auth_user()['id'],'create','project',$id,$title);
        flash_set('success','Project created.');
    }

    // Gallery upload (multi-file)
    if (!empty($_FILES['gallery']['name'][0])) {
        foreach ($_FILES['gallery']['name'] as $k => $name) {
            if (!$name) continue;
            $f = ['name'=>$name,'type'=>$_FILES['gallery']['type'][$k],'tmp_name'=>$_FILES['gallery']['tmp_name'][$k],'error'=>$_FILES['gallery']['error'][$k],'size'=>$_FILES['gallery']['size'][$k]];
            $u = upload_image($f, 'projects/' . $slug . '/gallery', $slug);
            if ($u) db_exec("INSERT INTO project_images (project_id,image_path,type) VALUES (?,?,'gallery')", [$id,$u]);
        }
    }

    redirect('/admin/projects/edit?id=' . $id);
}

$specsArr   = $p ? (json_decode($p['key_specs'] ?? '{}', true) ?: []) : [];
$amenities  = $p ? (json_decode($p['amenities'] ?? '[]', true) ?: []) : [];
$gallery    = $p ? db_all("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order", [$id]) : [];

ob_start(); ?>
<div class="page-header">
  <h1><?= $id ? 'Edit Project' : 'New Project' ?></h1>
  <a class="btn btn-ghost" href="/admin/projects">← Back</a>
</div>

<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="hero_existing" value="<?= e($p['hero_image'] ?? '') ?>">

  <div class="card">
    <div class="row">
      <div class="field"><label>Title *</label><input type="text" name="title" value="<?= e($p['title'] ?? '') ?>" required></div>
      <div class="field"><label>URL Slug</label><input type="text" name="slug" value="<?= e($p['slug'] ?? '') ?>" placeholder="auto from title"></div>
    </div>
    <div class="row">
      <div class="field"><label>Location</label><input type="text" name="location" value="<?= e($p['location'] ?? '') ?>"></div>
      <div class="field"><label>Type</label>
        <select name="type">
          <?php foreach (['Residential','Commercial','Mixed-Use'] as $t): ?>
            <option <?= ($p['type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="row">
      <div class="field"><label>Status</label>
        <select name="status">
          <?php foreach (['Ongoing','Completed'] as $s): ?>
            <option <?= ($p['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= (int)($p['sort_order'] ?? 0) ?>"></div>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;font-family:'Cormorant Garamond',serif;font-weight:400;">Content</h3>
    <div class="field"><label>Short Description (cards)</label><textarea name="short_description" rows="3"><?= e($p['short_description'] ?? '') ?></textarea></div>
    <div class="field"><label>Full Description (HTML allowed)</label><textarea name="full_description" rows="10"><?= e($p['full_description'] ?? '') ?></textarea><div class="help">Paste rich HTML, or just paragraphs. (Drop in TinyMCE later for WYSIWYG.)</div></div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;font-family:'Cormorant Garamond',serif;font-weight:400;">Hero & Gallery</h3>
    <div class="field">
      <label>Hero Image (replaces if uploaded)</label>
      <?php if (!empty($p['hero_image'])): ?>
        <div style="margin-bottom:.5rem;"><img src="<?= e($p['hero_image']) ?>" style="max-width:240px;border:1px solid var(--border)"></div>
      <?php endif; ?>
      <input type="file" name="hero" accept="image/*">
    </div>
    <?php if ($id): ?>
    <div class="field">
      <label>Add Gallery Images (multiple)</label>
      <input type="file" name="gallery[]" accept="image/*" multiple>
    </div>
    <?php if ($gallery): ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:.5rem;margin-top:1rem;">
        <?php foreach ($gallery as $g): ?>
          <div style="aspect-ratio:1;background:#000 url('<?= e($g['image_path']) ?>') center/cover;"></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3 style="margin-top:0;font-family:'Cormorant Garamond',serif;font-weight:400;">Key Specifications</h3>
    <div id="specs">
      <?php foreach ($specsArr as $k => $v): ?>
        <div class="row" style="margin-bottom:.5rem;">
          <input type="text" name="spec_key[]" placeholder="Key" value="<?= e($k) ?>">
          <input type="text" name="spec_val[]" placeholder="Value" value="<?= e(is_array($v)?implode(', ',$v):$v) ?>">
        </div>
      <?php endforeach; ?>
      <div class="row" style="margin-bottom:.5rem;">
        <input type="text" name="spec_key[]" placeholder="Key (e.g. floors)">
        <input type="text" name="spec_val[]" placeholder="Value (e.g. 14 Stories)">
      </div>
    </div>
    <button type="button" class="btn btn-ghost" onclick="addSpec()">+ Add Spec</button>
    <script>function addSpec(){const d=document.getElementById('specs');const r=document.createElement('div');r.className='row';r.style.marginBottom='.5rem';r.innerHTML='<input type=text name=spec_key[] placeholder=Key><input type=text name=spec_val[] placeholder=Value>';d.appendChild(r);}</script>
  </div>

  <div class="card">
    <h3 style="margin-top:0;font-family:'Cormorant Garamond',serif;font-weight:400;">Amenities</h3>
    <div class="field"><label>One per line</label><textarea name="amenities" rows="6"><?= e(implode("\n", $amenities)) ?></textarea></div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;font-family:'Cormorant Garamond',serif;font-weight:400;">SEO</h3>
    <div class="field"><label>SEO Title</label><input type="text" name="seo_title" value="<?= e($p['seo_title'] ?? '') ?>"></div>
    <div class="field"><label>SEO Description</label><textarea name="seo_description" rows="2"><?= e($p['seo_description'] ?? '') ?></textarea></div>
  </div>

  <div class="card">
    <label style="display:flex;align-items:center;gap:.5rem;text-transform:none;letter-spacing:0;font-size:.9rem;color:var(--text);margin-bottom:.75rem;">
      <input type="checkbox" name="is_published" <?= !empty($p['is_published']) ? 'checked' : '' ?>> Published
    </label>
    <label style="display:flex;align-items:center;gap:.5rem;text-transform:none;letter-spacing:0;font-size:.9rem;color:var(--text);">
      <input type="checkbox" name="is_featured" <?= !empty($p['is_featured']) ? 'checked' : '' ?>> Featured (show on homepage)
    </label>
  </div>

  <div class="actions">
    <a class="btn btn-ghost" href="/admin/projects">Cancel</a>
    <button class="btn btn-primary" type="submit"><?= $id ? 'Save Changes' : 'Create Project' ?></button>
  </div>
</form>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../_layout.php';
