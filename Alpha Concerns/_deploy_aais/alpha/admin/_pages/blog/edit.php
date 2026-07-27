<?php
$page_title = 'Edit Post';
$id = (int)($_GET['id'] ?? 0);
$p  = $id ? db_one("SELECT * FROM blog_posts WHERE id = ?", [$id]) : null;
if ($id && !$p) { flash_set('error','Post not found.'); redirect('/admin/blog'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
    $title    = trim($_POST['title'] ?? '');
    $slug     = slugify($_POST['slug'] ?? $title);
    $excerpt  = trim($_POST['excerpt'] ?? '');
    $body     = $_POST['body'] ?? '';
    $cat      = trim($_POST['category'] ?? '');
    $tags     = trim($_POST['tags'] ?? '');
    $author   = trim($_POST['author_name'] ?? '');
    $kw       = trim($_POST['primary_keyword'] ?? '');
    $seoTitle = trim($_POST['seo_title'] ?? '');
    $seoDesc  = trim($_POST['seo_description'] ?? '');
    $status   = $_POST['status'] ?? 'draft';
    $publishedAt = $_POST['published_at'] ?: null;
    $featured = $_POST['featured_existing'] ?? '';

    // FAQ
    $faqs = [];
    foreach (($_POST['faq_q'] ?? []) as $i => $q) {
        $q = trim($q); $a = trim($_POST['faq_a'][$i] ?? '');
        if ($q && $a) $faqs[] = ['q'=>$q,'a'=>$a];
    }

    if ($title === '') { flash_set('error','Title required.'); redirect($_SERVER['REQUEST_URI']); }

    if (!empty($_FILES['featured']['name'])) {
        $u = upload_image($_FILES['featured'], 'blog', $slug);
        if ($u) $featured = $u;
    }

    if ($id) {
        db_exec("UPDATE blog_posts SET title=?,slug=?,excerpt=?,body=?,featured_image=?,category=?,tags=?,author_name=?,primary_keyword=?,seo_title=?,seo_description=?,faq_schema=?,status=?,published_at=? WHERE id=?",
            [$title,$slug,$excerpt,$body,$featured,$cat,$tags,$author,$kw,$seoTitle,$seoDesc,json_encode($faqs),$status,$publishedAt,$id]);
        flash_set('success','Post updated.');
    } else {
        $id = db_insert("INSERT INTO blog_posts (title,slug,excerpt,body,featured_image,category,tags,author_name,primary_keyword,seo_title,seo_description,faq_schema,status,published_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [$title,$slug,$excerpt,$body,$featured,$cat,$tags,$author,$kw,$seoTitle,$seoDesc,json_encode($faqs),$status,$publishedAt]);
        flash_set('success','Post created.');
    }
    redirect('/admin/blog/edit?id=' . $id);
}

$faqArr = $p ? (json_decode($p['faq_schema'] ?? '[]', true) ?: []) : [];
ob_start(); ?>
<div class="page-header">
  <h1><?= $id ? 'Edit Post' : 'New Post' ?></h1>
  <a class="btn btn-ghost" href="/admin/blog">← Back</a>
</div>

<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="featured_existing" value="<?= e($p['featured_image'] ?? '') ?>">

  <div class="card">
    <div class="row">
      <div class="field"><label>Title *</label><input type="text" name="title" value="<?= e($p['title'] ?? '') ?>" required></div>
      <div class="field"><label>Slug</label><input type="text" name="slug" value="<?= e($p['slug'] ?? '') ?>"></div>
    </div>
    <div class="row">
      <div class="field"><label>Category</label><input type="text" name="category" value="<?= e($p['category'] ?? '') ?>" placeholder="e.g. Construction Tips"></div>
      <div class="field"><label>Tags (comma)</label><input type="text" name="tags" value="<?= e($p['tags'] ?? '') ?>"></div>
    </div>
    <div class="row">
      <div class="field"><label>Author</label><input type="text" name="author_name" value="<?= e($p['author_name'] ?? '') ?>"></div>
      <div class="field"><label>Status</label>
        <select name="status">
          <?php foreach (['draft','published','scheduled'] as $s): ?>
            <option <?= ($p['status'] ?? 'draft') === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="field"><label>Publish Date</label><input type="datetime-local" name="published_at" value="<?= e($p['published_at'] ? date('Y-m-d\TH:i', strtotime($p['published_at'])) : '') ?>"></div>
  </div>

  <div class="card">
    <div class="field"><label>Excerpt (used on cards / OG)</label><textarea name="excerpt" rows="2"><?= e($p['excerpt'] ?? '') ?></textarea></div>
    <div class="field"><label>Body (HTML)</label><textarea name="body" rows="18"><?= e($p['body'] ?? '') ?></textarea><div class="help">Drop in TinyMCE here later. For now, write HTML or Markdown-converted HTML.</div></div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;font-family:'Cormorant Garamond',serif;font-weight:400;">Featured Image</h3>
    <?php if (!empty($p['featured_image'])): ?>
      <div style="margin-bottom:.5rem;"><img src="<?= e($p['featured_image']) ?>" style="max-width:240px;border:1px solid var(--border)"></div>
    <?php endif; ?>
    <input type="file" name="featured" accept="image/*">
  </div>

  <div class="card">
    <h3 style="margin-top:0;font-family:'Cormorant Garamond',serif;font-weight:400;">SEO & AEO</h3>
    <div class="row">
      <div class="field"><label>Primary Keyword</label><input type="text" name="primary_keyword" value="<?= e($p['primary_keyword'] ?? '') ?>"></div>
      <div class="field"><label>SEO Title</label><input type="text" name="seo_title" value="<?= e($p['seo_title'] ?? '') ?>"></div>
    </div>
    <div class="field"><label>SEO Description</label><textarea name="seo_description" rows="2"><?= e($p['seo_description'] ?? '') ?></textarea></div>

    <hr>
    <label>FAQ Schema (for AEO snippets)</label>
    <div id="faqs">
      <?php foreach ($faqArr as $f): ?>
      <div style="margin-bottom:.5rem;">
        <input type="text" name="faq_q[]" placeholder="Question" value="<?= e($f['q']) ?>" style="margin-bottom:.25rem;">
        <textarea name="faq_a[]" placeholder="Answer" rows="2"><?= e($f['a']) ?></textarea>
      </div>
      <?php endforeach; ?>
      <div style="margin-bottom:.5rem;">
        <input type="text" name="faq_q[]" placeholder="Question" style="margin-bottom:.25rem;">
        <textarea name="faq_a[]" placeholder="Answer" rows="2"></textarea>
      </div>
    </div>
    <button type="button" class="btn btn-ghost" onclick="addFaq()">+ Add FAQ</button>
    <script>function addFaq(){const d=document.getElementById('faqs');const w=document.createElement('div');w.style.marginBottom='.5rem';w.innerHTML='<input type=text name=faq_q[] placeholder=Question style=margin-bottom:.25rem;><textarea name=faq_a[] placeholder=Answer rows=2></textarea>';d.appendChild(w);}</script>
  </div>

  <div class="actions">
    <a class="btn btn-ghost" href="/admin/blog">Cancel</a>
    <button class="btn btn-primary" type="submit"><?= $id ? 'Save Changes' : 'Create Post' ?></button>
  </div>
</form>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../_layout.php';
