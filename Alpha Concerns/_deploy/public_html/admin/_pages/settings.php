<?php
$page_title = 'Site Settings';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify_or_die();
    foreach (($_POST['s'] ?? []) as $k => $v) {
        $k = preg_replace('/[^a-z0-9_]/','', $k);
        $upsert = DB_DRIVER === 'sqlite'
    ? "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value"
    : "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        db_exec($upsert, [$k, $v]);
    }

    // Hero image upload
    if (!empty($_FILES['hero_image']['name'])) {
        $u = upload_image($_FILES['hero_image'], 'misc', 'hero');
        if ($u) {
            $up2 = DB_DRIVER === 'sqlite'
                ? "INSERT INTO site_settings (setting_key, setting_value) VALUES ('hero_image', ?) ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value"
                : "INSERT INTO site_settings (setting_key, setting_value) VALUES ('hero_image', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
            db_exec($up2, [$u]);
        }
    }

    flash_set('success','Settings saved.');
    redirect('/admin/settings');
}

// Reload cache by re-querying directly
$all = [];
foreach (db_all("SELECT setting_key, setting_value FROM site_settings") as $r) {
    $all[$r['setting_key']] = $r['setting_value'];
}
ob_start(); ?>
<div class="page-header"><h1>Site Settings</h1></div>
<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="card">
    <h3 style="margin-top:0;font-family:'Cormorant Garamond',serif;font-weight:400;">Company</h3>
    <div class="row">
      <div class="field"><label>Company Name</label><input name="s[company_name]" value="<?= e($all['company_name']??'') ?>"></div>
      <div class="field"><label>Tagline</label><input name="s[tagline]" value="<?= e($all['tagline']??'') ?>"></div>
    </div>
    <div class="field"><label>Description</label><textarea name="s[description]" rows="2"><?= e($all['description']??'') ?></textarea></div>
    <div class="row">
      <div class="field"><label>Address</label><input name="s[address]" value="<?= e($all['address']??'') ?>"></div>
      <div class="field"><label>Office Hours</label><input name="s[office_hours]" value="<?= e($all['office_hours']??'') ?>"></div>
    </div>
    <div class="row">
      <div class="field"><label>Primary Phone</label><input name="s[phone_primary]" value="<?= e($all['phone_primary']??'') ?>"></div>
      <div class="field"><label>Primary Email</label><input name="s[email_primary]" value="<?= e($all['email_primary']??'') ?>"></div>
    </div>
    <div class="field"><label>WhatsApp Number (no +)</label><input name="s[whatsapp_number]" value="<?= e($all['whatsapp_number']??'') ?>" placeholder="9779812345678"></div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;font-family:'Cormorant Garamond',serif;font-weight:400;">Social</h3>
    <div class="row">
      <div class="field"><label>Facebook URL</label><input name="s[social_facebook]" value="<?= e($all['social_facebook']??'') ?>"></div>
      <div class="field"><label>Instagram URL</label><input name="s[social_instagram]" value="<?= e($all['social_instagram']??'') ?>"></div>
    </div>
    <div class="row">
      <div class="field"><label>LinkedIn URL</label><input name="s[social_linkedin]" value="<?= e($all['social_linkedin']??'') ?>"></div>
      <div class="field"><label>YouTube URL</label><input name="s[social_youtube]" value="<?= e($all['social_youtube']??'') ?>"></div>
    </div>
    <div class="field"><label>Google Maps Embed URL</label><input name="s[map_embed_url]" value="<?= e($all['map_embed_url']??'') ?>"></div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;font-family:'Cormorant Garamond',serif;font-weight:400;">Homepage</h3>
    <div class="field"><label>Hero Headline</label><input name="s[hero_headline]" value="<?= e($all['hero_headline']??'') ?>"></div>
    <div class="field"><label>Hero Subheadline</label><textarea name="s[hero_subheadline]" rows="2"><?= e($all['hero_subheadline']??'') ?></textarea></div>
    <div class="field">
      <label>Hero Image (upload to replace)</label>
      <?php if (!empty($all['hero_image'])): ?><div style="margin-bottom:.5rem;"><img src="<?= e($all['hero_image']) ?>" style="max-width:240px;border:1px solid var(--border)"></div><?php endif; ?>
      <input type="file" name="hero_image" accept="image/*">
    </div>
    <div class="field"><label>About Snapshot</label><textarea name="s[about_snapshot]" rows="3"><?= e($all['about_snapshot']??'') ?></textarea></div>

    <div class="row">
      <div class="field"><label>Years (Stat)</label><input type="number" name="s[stat_years]" value="<?= e($all['stat_years']??'10') ?>"></div>
      <div class="field"><label>Projects (Stat)</label><input type="number" name="s[stat_projects]" value="<?= e($all['stat_projects']??'50') ?>"></div>
    </div>
    <div class="row">
      <div class="field"><label>Clients (Stat)</label><input type="number" name="s[stat_clients]" value="<?= e($all['stat_clients']??'200') ?>"></div>
      <div class="field"><label>Team (Stat)</label><input type="number" name="s[stat_team]" value="<?= e($all['stat_team']??'80') ?>"></div>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;font-family:'Cormorant Garamond',serif;font-weight:400;">SEO & Analytics</h3>
    <div class="field"><label>Default SEO Description</label><textarea name="s[seo_default_description]" rows="2"><?= e($all['seo_default_description']??'') ?></textarea></div>
    <div class="row">
      <div class="field"><label>Default OG Image (URL)</label><input name="s[seo_default_og_image]" value="<?= e($all['seo_default_og_image']??'') ?>"></div>
      <div class="field"><label>GA4 Measurement ID</label><input name="s[ga4_measurement_id]" value="<?= e($all['ga4_measurement_id']??'') ?>" placeholder="G-XXXXXXXXXX"></div>
    </div>
    <div class="field"><label>Search Console Verification Token</label><input name="s[gsc_verification]" value="<?= e($all['gsc_verification']??'') ?>"></div>
  </div>

  <div class="actions"><button class="btn btn-primary" type="submit">Save Settings</button></div>
</form>
<?php $content = ob_get_clean(); require __DIR__ . '/../_layout.php';
