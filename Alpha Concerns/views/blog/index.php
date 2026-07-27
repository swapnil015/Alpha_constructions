<?php
partial('head', compact('page_title'));
partial('header');
$cat   = $_GET['cat'] ?? '';
$where = "status='published'";
$args  = [];
if ($cat) { $where .= " AND category = ?"; $args[] = $cat; }
$posts = db_all("SELECT * FROM blog_posts WHERE $where ORDER BY published_at DESC LIMIT 24", $args);
$cats  = db_all("SELECT DISTINCT category FROM blog_posts WHERE status='published' AND category IS NOT NULL");
?>
<main id="main">
  <section class="hero" style="min-height: 55vh;">
    <div class="hero__bg"></div>
    <div class="container hero__inner">
      <div class="eyebrow">Insights</div>
      <h1 class="hero__title" data-split-words>From the field</h1>
      <p class="hero__sub reveal">Notes on construction, design, and the Kathmandu real-estate market.</p>
    </div>
  </section>

  <section class="section section--cream">
    <div class="container">
      <div class="filter-tabs">
        <a href="/blog" class="filter-tab <?= !$cat ? 'is-active' : '' ?>">All</a>
        <?php foreach ($cats as $c): ?>
          <a href="/blog?cat=<?= urlencode($c['category']) ?>" class="filter-tab <?= $cat === $c['category'] ? 'is-active' : '' ?>"><?= e($c['category']) ?></a>
        <?php endforeach; ?>
      </div>

      <div class="cards-grid" style="margin-top:0;">
        <?php foreach ($posts as $b): ?>
        <a href="/blog/<?= e($b['slug']) ?>" class="service-card reveal" style="aspect-ratio: 4/5; display:flex; flex-direction:column; justify-content:flex-end;">
          <div class="eyebrow" style="margin-bottom:1rem;"><?= e($b['category']) ?></div>
          <h2 style="font-family:var(--font-display); font-weight:400; font-size:1.5rem; line-height:1.2; color:var(--color-text-primary); margin-bottom:1rem;"><?= e($b['title']) ?></h2>
          <p class="service-card__desc"><?= e(excerpt($b['excerpt'] ?: $b['body'], 22)) ?></p>
          <div style="margin-top:1rem; font-size:.75rem; color:var(--color-text-muted); letter-spacing:.15em; text-transform:uppercase;">
            <?= fmt_date($b['published_at']) ?> · <?= read_time($b['body']) ?> min read
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php partial('footer'); ?>
