<?php
$page_title = 'Services';

// ---- Actions ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $act = $_POST['action'] ?? '';

    if ($act === 'save') {
        $id    = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $slug  = trim($_POST['slug'] ?? '');
        if ($slug === '') $slug = slugify($title);
        $desc  = trim($_POST['description'] ?? '');
        $full  = trim($_POST['full_content'] ?? '');
        $sort  = (int)($_POST['sort_order'] ?? 0);
        $on    = isset($_POST['is_active']) ? 1 : 0;

        if ($title === '') {
            flash_set('error', 'Title is required.');
            redirect('/admin/services');
        }

        $img = null;
        if (!empty($_FILES['hero_image']['name'])) {
            $img = upload_image($_FILES['hero_image'], 'services', $slug);
        }

        if ($id > 0) {
            db_exec("UPDATE services SET title=?, slug=?, description=?, full_content=?, sort_order=?, is_active=? WHERE id=?",
                [$title, $slug, $desc, $full, $sort, $on, $id]);
            if ($img) db_exec("UPDATE services SET hero_image=? WHERE id=?", [$img, $id]);
        } else {
            db_insert("INSERT INTO services (title, slug, description, full_content, hero_image, sort_order, is_active) VALUES (?,?,?,?,?,?,?)",
                [$title, $slug, $desc, $full, $img ?? '', $sort, $on]);
        }
        flash_set('success', 'Service saved.');
        redirect('/admin/services');
    }

    if ($act === 'delete') {
        db_exec("DELETE FROM services WHERE id=?", [(int)($_POST['id'] ?? 0)]);
        flash_set('success', 'Service deleted.');
        redirect('/admin/services');
    }
}

// ---- Data ------------------------------------------------------------------
$editing = null;
if (isset($_GET['id'])) {
    $editing = db_one("SELECT * FROM services WHERE id=?", [(int)$_GET['id']]);
}
$rows = db_all("SELECT * FROM services ORDER BY sort_order, id");

ob_start(); ?>
<div class="page-header">
  <h1>Services</h1>
  <a class="btn" href="/admin/services">+ New Service</a>
</div>

<div class="card">
  <h3 style="margin-top:0;font-family:'Cormorant Garamond',serif;font-weight:400;">
    <?= $editing ? 'Edit: ' . e($editing['title']) : 'Add Service' ?>
  </h3>
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)($editing['id'] ?? 0) ?>">
    <div class="row">
      <div class="field"><label>Title</label><input name="title" required value="<?= e($editing['title'] ?? '') ?>"></div>
      <div class="field"><label>Slug (URL)</label><input name="slug" value="<?= e($editing['slug'] ?? '') ?>" placeholder="auto from title"></div>
    </div>
    <div class="field"><label>Short Description (cards, homepage)</label>
      <textarea name="description" rows="2"><?= e($editing['description'] ?? '') ?></textarea></div>
    <div class="field"><label>Full Content (service detail page)</label>
      <textarea name="full_content" rows="8"><?= e($editing['full_content'] ?? '') ?></textarea></div>
    <div class="row">
      <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= (int)($editing['sort_order'] ?? (count($rows) + 1)) ?>"></div>
      <div class="field"><label>Image (homepage panel + detail hero)</label><input type="file" name="hero_image" accept="image/*"></div>
    </div>
    <?php if (!empty($editing['hero_image'])): ?>
      <div class="field"><img src="<?= e($editing['hero_image']) ?>" alt="" style="max-height:90px;border-radius:4px;"></div>
    <?php endif; ?>
    <label style="display:flex;gap:.5rem;align-items:center;margin-bottom:1rem;">
      <input type="checkbox" name="is_active" <?= ($editing['is_active'] ?? 1) ? 'checked' : '' ?>> Active
    </label>
    <button class="btn btn-primary">Save Service</button>
  </form>
</div>

<div class="card">
  <table class="table">
    <thead><tr><th>#</th><th>Title</th><th>Slug</th><th>Image</th><th>Active</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['sort_order'] ?></td>
        <td><?= e($r['title']) ?></td>
        <td style="color:var(--muted)"><?= e($r['slug']) ?></td>
        <td><?= $r['hero_image'] ? '<img src="' . e($r['hero_image']) . '" style="height:34px;border-radius:3px;">' : '<span style="color:var(--mute2)">—</span>' ?></td>
        <td><?= $r['is_active'] ? 'Yes' : 'No' ?></td>
        <td style="text-align:right;white-space:nowrap;">
          <a class="btn" href="/admin/services?id=<?= (int)$r['id'] ?>">Edit</a>
          <form method="post" style="display:inline" onsubmit="return confirm('Delete this service?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../_layout.php'; ?>
