<?php
partial('head', compact('page_title'));
partial('header');
$projects = db_all("SELECT * FROM projects WHERE is_published=1 ORDER BY sort_order");

/*
 * Grouped by delivery status rather than shown as one flat grid. Ordered so a
 * visitor meets live work first. A group with no projects is skipped entirely,
 * so this stays correct as the portfolio changes in the admin panel.
 */
$groups = [
    'Ongoing'   => ['label' => 'Ongoing Projects',   'note' => 'Currently on site'],
    'Completed' => ['label' => 'Completed Projects', 'note' => 'Delivered and handed over'],
    'Upcoming'  => ['label' => 'Upcoming Projects',  'note' => 'In design and verification'],
];
$byStatus = [];
foreach ($projects as $p) { $byStatus[$p['status']][] = $p; }

$page_scripts = '<script src="' . asset('assets/js/projects-list.js') . '" defer></script>';
?>
<main id="main">
  <section class="hero" style="min-height: 60vh;">
    <div class="hero__bg"></div>
    <div class="container hero__inner">
      <div class="eyebrow">Portfolio</div>
      <h1 class="hero__title" data-masked>Selected projects</h1>
      <p class="hero__sub reveal">Residential, commercial and institutional buildings across Nepal — designed, built and handed over.</p>
    </div>
  </section>

  <section class="section section--cream">
    <div class="container" data-pg>
      <?php foreach ($groups as $status => $meta):
        $items = $byStatus[$status] ?? [];
        if (!$items) continue; ?>
      <div class="pg__group">
        <div class="pg__groupHead">
          <h2 class="pg__groupTitle"><?= e($meta['label']) ?></h2>
          <span class="pg__count"><?= count($items) ?> <?= count($items) === 1 ? 'project' : 'projects' ?></span>
        </div>

        <div class="projects-grid">
          <?php foreach ($items as $p):
            $img = trim((string)$p['hero_image']); ?>
          <a href="/projects/<?= e($p['slug']) ?>" class="project-card pg__card" data-pg-card>
            <div class="project-card__img<?= $img === '' ? ' bg-placeholder' : '' ?>"
                 <?php if ($img !== ''): ?>style="background-image: linear-gradient(135deg,rgba(0,30,30,.35),rgba(0,24,24,.78)), url('<?= e($img) ?>');"<?php endif; ?>></div>
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
      <?php endforeach; ?>
    </div>
  </section>
</main>
<?php partial('footer', compact('page_scripts')); ?>
