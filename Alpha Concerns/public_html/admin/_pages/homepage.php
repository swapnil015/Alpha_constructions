<?php
$page_title = 'Homepage';

/**
 * Edits the homepage sections that are not backed by their own table:
 * hero copy lives in Site Settings already; this page covers the story
 * timeline, the two Current Projects panels and the six Why cards.
 * Values are stored in site_settings; views/home.php falls back to its
 * built-in copy for any key left empty.
 */

$upsert = function (string $k, string $v): void {
    $sql = DB_DRIVER === 'sqlite'
        ? "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value"
        : "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    db_exec($sql, [$k, $v]);
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    foreach (($_POST['s'] ?? []) as $k => $v) {
        $k = preg_replace('/[^a-z0-9_]/', '', $k);
        $v = trim($v);
        // An empty field means "use the built-in copy". setting() returns a
        // stored empty string as-is, so the key must be removed, not saved
        // empty, for the fallback to apply.
        if ($v === '') {
            db_exec("DELETE FROM site_settings WHERE setting_key = ?", [$k]);
        } else {
            $upsert($k, $v);
        }
    }
    // Poster uploads for the two project panels
    foreach (['cp1_poster', 'cp2_poster'] as $key) {
        if (!empty($_FILES[$key]['name'])) {
            $u = upload_image($_FILES[$key], 'projects', $key);
            if ($u) $upsert($key, $u);
        }
    }
    flash_set('success', 'Homepage content saved.');
    redirect('/admin/homepage');
}

$all = [];
foreach (db_all("SELECT setting_key, setting_value FROM site_settings") as $r) {
    $all[$r['setting_key']] = $r['setting_value'];
}
$v = fn(string $k, string $d = '') => e($all[$k] ?? $d);

ob_start(); ?>
<div class="page-header"><h1>Homepage Sections</h1></div>
<p style="color:var(--muted);margin-top:-.5rem;margin-bottom:1.25rem;">
  Empty fields fall back to the site's built-in copy. Hero headline, stats and
  contact details live under <a href="/admin/settings" style="color:var(--accent)">Site Settings</a>;
  service cards under <a href="/admin/services" style="color:var(--accent)">Services</a>.
</p>

<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="card">
    <h3 style="margin-top:0;font-family:'Cormorant Garamond',serif;font-weight:400;">Story Timeline ("A building is drawn…")</h3>
    <div class="field"><label>Headline (plain text; leave empty to keep the styled default)</label>
      <input name="s[story_headline]" value="<?= $v('story_headline') ?>" placeholder="A building is drawn long before it is built."></div>
    <?php foreach ([1 => 'Design', 2 => 'Engineer', 3 => 'Deliver'] as $i => $ph): ?>
    <div class="row">
      <div class="field" style="max-width:220px;"><label>Block <?= $i ?> label</label>
        <input name="s[story_eyebrow_<?= $i ?>]" value="<?= $v("story_eyebrow_$i") ?>" placeholder="0<?= $i ?> — <?= $ph ?>"></div>
      <div class="field"><label>Block <?= $i ?> text</label>
        <textarea name="s[story_text_<?= $i ?>]" rows="2"><?= $v("story_text_$i") ?></textarea></div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php foreach ([1, 2] as $n): ?>
  <div class="card">
    <h3 style="margin-top:0;font-family:'Cormorant Garamond',serif;font-weight:400;">Current Project Panel <?= $n ?></h3>
    <div class="row">
      <div class="field"><label>Name</label><input name="s[cp<?= $n ?>_name]" value="<?= $v("cp{$n}_name") ?>"></div>
      <div class="field"><label>Category</label><input name="s[cp<?= $n ?>_category]" value="<?= $v("cp{$n}_category") ?>" placeholder="Residential"></div>
    </div>
    <div class="row">
      <div class="field"><label>Location</label><input name="s[cp<?= $n ?>_location]" value="<?= $v("cp{$n}_location") ?>"></div>
      <div class="field"><label>Status badge</label><input name="s[cp<?= $n ?>_status]" value="<?= $v("cp{$n}_status") ?>" placeholder="Ongoing"></div>
    </div>
    <div class="row">
      <div class="field"><label>Link slug (/projects/…)</label><input name="s[cp<?= $n ?>_slug]" value="<?= $v("cp{$n}_slug") ?>"></div>
      <div class="field"><label>Video path (mp4, optional)</label><input name="s[cp<?= $n ?>_video]" value="<?= $v("cp{$n}_video") ?>" placeholder="/assets/video/….mp4"></div>
    </div>
    <div class="field"><label>Poster / still image</label><input type="file" name="cp<?= $n ?>_poster" accept="image/*"></div>
    <?php if (!empty($all["cp{$n}_poster"])): ?>
      <div class="field"><img src="<?= e($all["cp{$n}_poster"]) ?>" style="max-height:80px;border-radius:4px;" alt=""></div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>

  <div class="card">
    <h3 style="margin-top:0;font-family:'Cormorant Garamond',serif;font-weight:400;">Why Alpha Concern (six cards)</h3>
    <?php for ($i = 1; $i <= 6; $i++): ?>
    <div class="row">
      <div class="field" style="max-width:260px;"><label>Card <?= $i ?> title</label>
        <input name="s[why<?= $i ?>_title]" value="<?= $v("why{$i}_title") ?>"></div>
      <div class="field"><label>Card <?= $i ?> text</label>
        <textarea name="s[why<?= $i ?>_desc]" rows="2"><?= $v("why{$i}_desc") ?></textarea></div>
    </div>
    <?php endfor; ?>
  </div>

  <button class="btn btn-primary">Save Homepage Content</button>
</form>
<?php $content = ob_get_clean(); require __DIR__ . '/../_layout.php'; ?>
