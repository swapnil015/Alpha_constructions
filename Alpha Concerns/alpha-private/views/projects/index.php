<?php
partial('head', compact('page_title'));
partial('header');
$projects = db_all("SELECT * FROM projects WHERE is_published=1 ORDER BY sort_order");
?>
<main id="main">
  <section class="hero" style="min-height: 60vh;">
    <div class="hero__bg"></div>
    <div class="container hero__inner">
      <div class="eyebrow">Portfolio</div>
      <h1 class="hero__title" data-split-words>Selected projects</h1>
      <p class="hero__sub reveal">A decade of building — residential, commercial, and mixed-use, across the Kathmandu Valley.</p>
    </div>
  </section>

  <section class="section section--cream">
    <div class="container" data-filter-group>
      <div class="filter-tabs">
        <button class="filter-tab is-active" data-cat="all">All</button>
        <button class="filter-tab" data-cat="Residential">Residential</button>
        <button class="filter-tab" data-cat="Commercial">Commercial</button>
        <button class="filter-tab" data-cat="Mixed-Use">Mixed-Use</button>
      </div>
      <div class="projects-grid">
        <?php foreach ($projects as $p): ?>
        <a href="/projects/<?= e($p['slug']) ?>" class="project-card reveal" data-cat="<?= e($p['type']) ?>">
          <div class="project-card__img" style="background-image: linear-gradient(135deg,rgba(13,27,42,.4),rgba(13,27,42,.7)), url('<?= e($p['hero_image']) ?>');"></div>
          <span class="project-card__badge project-card__badge--<?= strtolower($p['status']) ?>"><?= e($p['status']) ?></span>
          <div class="project-card__overlay">
            <div class="project-card__type"><?= e($p['type']) ?></div>
            <div class="project-card__title"><?= e($p['title']) ?></div>
            <div class="project-card__location"><?= e($p['location']) ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php partial('footer'); ?>
