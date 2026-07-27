<?php
$page_title = $service['title'];
$page_description = $service['seo_description'] ?: $service['description'];
partial('head', compact('page_title','page_description'));
partial('header');
$relatedProjects = db_all("SELECT * FROM projects WHERE is_published=1 ORDER BY is_featured DESC, sort_order LIMIT 3");
?>
<main id="main">
  <section class="hero" style="min-height: 65vh;">
    <div class="hero__bg"></div>
    <div class="container hero__inner">
      <div class="eyebrow">Service</div>
      <h1 class="hero__title" data-split-words><?= e($service['title']) ?></h1>
      <p class="hero__sub reveal"><?= e($service['description']) ?></p>
    </div>
  </section>

  <section class="section section--cream">
    <div class="container split">
      <div>
        <div class="eyebrow reveal">Overview</div>
        <h2 class="display display-md reveal" style="margin: 1rem 0 1.5rem;">What's involved</h2>
        <div class="reveal"><?= $service['full_content'] ?: '<p>' . e($service['description']) . '</p>' ?></div>
      </div>
      <div>
        <div class="service-card reveal">
          <div class="eyebrow" style="margin-bottom:1rem;">Includes</div>
          <ul style="list-style:none; padding:0;">
            <?php foreach (['Site assessment & feasibility','Concept & detailed design','Permitting & approvals','Construction & QA/QC','Handover & warranty'] as $item): ?>
              <li style="display:flex; gap:.75rem; margin-bottom:.85rem; color:var(--color-text-secondary);"><span style="color:var(--color-accent);">✓</span> <?= e($item) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <a href="/contact?subject=<?= rawurlencode('Quote Request - ' . $service['title']) ?>" class="btn btn-primary reveal" style="margin-top:1.5rem; width:100%; justify-content:center;">Request a Quote</a>
      </div>
    </div>
  </section>

  <?php if ($relatedProjects): ?>
  <section class="section section--surface">
    <div class="container">
      <div style="text-align:center; margin-bottom:3rem;">
        <div class="eyebrow reveal">Related Work</div>
        <h2 class="display display-lg reveal" style="margin-top:1rem;">Projects we've delivered</h2>
      </div>
      <div class="projects-grid">
        <?php foreach ($relatedProjects as $p): ?>
        <a href="/projects/<?= e($p['slug']) ?>" class="project-card reveal">
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
  <?php endif; ?>
</main>
<?php partial('footer'); ?>
