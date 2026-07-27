<?php
$page_title = 'Team';

// ---- Actions ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $act = $_POST['action'] ?? '';

    if ($act === 'save') {
        $id    = (int)($_POST['id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $bio   = trim($_POST['bio'] ?? '');
        $sort  = (int)($_POST['sort_order'] ?? 0);
        $on    = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            flash_set('error', 'Name is required.');
            redirect('/admin/team');
        }

        $photo = null;
        if (!empty($_FILES['photo']['name'])) {
            $photo = upload_image($_FILES['photo'], 'team', slugify($name));
        }

        if ($id > 0) {
            db_exec("UPDATE team_members SET name=?, title=?, bio=?, sort_order=?, is_active=? WHERE id=?",
                [$name, $title, $bio, $sort, $on, $id]);
            if ($photo) db_exec("UPDATE team_members SET photo=? WHERE id=?", [$photo, $id]);
        } else {
            db_insert("INSERT INTO team_members (name, title, bio, photo, sort_order, is_active) VALUES (?,?,?,?,?,?)",
                [$name, $title, $bio, $photo ?? '', $sort, $on]);
        }
        flash_set('success', 'Team member saved.');
        redirect('/admin/team');
    }

    if ($act === 'delete') {
        db_exec("DELETE FROM team_members WHERE id=?", [(int)($_POST['id'] ?? 0)]);
        flash_set('success', 'Team member deleted.');
        redirect('/admin/team');
    }
}

// ---- Data ------------------------------------------------------------------
$editing = null;
if (isset($_GET['id'])) {
    $editing = db_one("SELECT * FROM team_members WHERE id=?", [(int)$_GET['id']]);
}
$rows = db_all("SELECT * FROM team_members ORDER BY sort_order, id");

ob_start(); ?>
<div class="page-header">
  <h1>Team</h1>
  <a class="btn" href="/admin/team">+ New Member</a>
</div>

<div class="card">
  <h3 style="margin-top:0;font-family:'Cormorant Garamond',serif;font-weight:400;">
    <?= $editing ? 'Edit: ' . e($editing['name']) : 'Add Member' ?>
  </h3>
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)($editing['id'] ?? 0) ?>">
    <div class="row">
      <div class="field"><label>Name</label><input name="name" required value="<?= e($editing['name'] ?? '') ?>"></div>
      <div class="field"><label>Role / Title</label><input name="title" value="<?= e($editing['title'] ?? '') ?>" placeholder="Managing Director"></div>
    </div>
    <div class="field"><label>Department / Short Bio (shown under the name)</label>
      <textarea name="bio" rows="2"><?= e($editing['bio'] ?? '') ?></textarea></div>
    <div class="row">
      <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= (int)($editing['sort_order'] ?? (count($rows) + 1)) ?>"></div>
      <div class="field"><label>Portrait (square works best)</label><input type="file" name="photo" accept="image/*"></div>
    </div>
    <?php if (!empty($editing['photo'])): ?>
      <div class="field"><img src="<?= e($editing['photo']) ?>" alt="" style="height:90px;border-radius:4px;"></div>
    <?php endif; ?>
    <label style="display:flex;gap:.5rem;align-items:center;margin-bottom:1rem;">
      <input type="checkbox" name="is_active" <?= ($editing['is_active'] ?? 1) ? 'checked' : '' ?>> Active
    </label>
    <button class="btn btn-primary">Save Member</button>
  </form>
</div>

<div class="card">
  <table class="table">
    <thead><tr><th>#</th><th>Photo</th><th>Name</th><th>Role</th><th>Active</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['sort_order'] ?></td>
        <td><?= $r['photo'] ? '<img src="' . e($r['photo']) . '" style="height:38px;width:38px;object-fit:cover;border-radius:50%;">' : '<span style="color:var(--mute2)">—</span>' ?></td>
        <td><?= e($r['name']) ?></td>
        <td style="color:var(--muted)"><?= e($r['title']) ?></td>
        <td><?= $r['is_active'] ? 'Yes' : 'No' ?></td>
        <td style="text-align:right;white-space:nowrap;">
          <a class="btn" href="/admin/team?id=<?= (int)$r['id'] ?>">Edit</a>
          <form method="post" style="display:inline" onsubmit="return confirm('Delete this member?')">
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
