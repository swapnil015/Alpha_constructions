<?php
partial('head', compact('page_title'));
partial('header');
$cat   = $_GET['cat'] ?? '';
$where = "status='published'";
$args  = [];
if ($cat) { $where .= " AND category = ?"; $args[] = $cat; }
$posts = db_all("SELECT * FROM blog_posts WHERE $where ORDER BY published_at DESC LIMIT 24", $args);
$cats  = db_all("SELECT DISTINCT category FROM blog_posts WHERE status='published' AND category IS NOT NULL");

// The newest post leads; the rest run as an editorial index. A grid of
// identical empty cards reads as unfinished when there are only a few posts.
$lead = $posts ? array_shift($posts) : null;
?>
<main id="main">
  <section class="hero hero--shot pg-hero in-hero">
    <img class="hero--shot__img" src="<?= asset('assets/img/hero/insights-skyline.jpg') ?>"
         alt="Two engineers on the top slab of a rising concrete frame at sunset, a tower crane above and the valley beyond."
         width="1681" height="935" fetchpriority="high" decoding="async">
    <span class="hero--shot__scrim" aria-hidden="true"></span>
    <div class="container hero__inner hero--shot__inner">
      <div class="eyebrow">Insights</div>
      <h1 class="hero__title" data-masked>From the field</h1>
      <p class="hero__sub reveal" data-reveal="up">Notes on construction, design, and the Kathmandu real-estate market.</p>
    </div>
  </section>

  <section class="section section--cream">
    <div class="container">
      <div class="filter-tabs reveal" data-reveal="up">
        <a href="/blog" class="filter-tab <?= !$cat ? 'is-active' : '' ?>">All</a>
        <?php foreach ($cats as $c): ?>
          <a href="/blog?cat=<?= urlencode($c['category']) ?>" class="filter-tab <?= $cat === $c['category'] ? 'is-active' : '' ?>"><?= e($c['category']) ?></a>
        <?php endforeach; ?>
      </div>

      <?php if ($lead): ?>
      <a class="in-lead reveal" data-reveal="up" href="/blog/<?= e($lead['slug']) ?>">
        <div class="in-lead__media">
          <img src="<?= asset('assets/img/story/apartment-dusk.jpg') ?>" alt="" loading="lazy" decoding="async" width="1325" height="881">
        </div>
        <div>
          <div class="eyebrow"><?= e($lead['category'] ?: 'Insight') ?></div>
          <h2 class="in-lead__title"><?= e($lead['title']) ?></h2>
          <p style="max-width:52ch;"><?= e(excerpt($lead['excerpt'] ?: $lead['body'], 32)) ?></p>
          <div class="in-meta" style="margin-top:1.25rem;">
            <?= fmt_date($lead['published_at']) ?> · <?= read_time($lead['body']) ?> min read
          </div>
        </div>
      </a>
      <?php endif; ?>

      <div class="in-list reveal--stagger">
        <?php foreach ($posts as $i => $b): ?>
        <a class="in-item reveal" data-reveal="up" href="/blog/<?= e($b['slug']) ?>">
          <span class="in-item__idx"><?= str_pad((string)($i + 2), 2, '0', STR_PAD_LEFT) ?></span>
          <div>
            <div class="eyebrow" style="margin-bottom:.6rem;"><?= e($b['category'] ?: 'Insight') ?></div>
            <h2 class="in-item__title"><?= e($b['title']) ?></h2>
            <p class="in-item__desc"><?= e(excerpt($b['excerpt'] ?: $b['body'], 24)) ?></p>
            <div class="in-meta" style="margin-top:.75rem;">
              <?= fmt_date($b['published_at']) ?> · <?= read_time($b['body']) ?> min read
            </div>
          </div>
          <span class="in-item__go" aria-hidden="true">&rarr;</span>
        </a>
        <?php endforeach; ?>
      </div>

      <?php if (!$lead): ?>
      <div class="cr-empty reveal" data-reveal="up">
        <p style="margin:0;">No articles published yet.</p>
      </div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php partial('footer'); ?>
