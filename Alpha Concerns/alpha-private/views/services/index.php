<?php
partial('head', compact('page_title'));
partial('header');
$services = db_all("SELECT * FROM services WHERE is_active=1 ORDER BY sort_order");
?>
<main id="main">
  <section class="hero" style="min-height: 60vh;">
    <div class="hero__bg"></div>
    <div class="container hero__inner">
      <div class="eyebrow">Services</div>
      <h1 class="hero__title" data-split-words>What we build</h1>
      <p class="hero__sub reveal">A complete construction practice — from structural engineering to interior finishing.</p>
    </div>
  </section>
  <section class="section section--cream">
    <div class="container">
      <div class="cards-grid" style="margin-top:0;">
        <?php foreach ($services as $s): ?>
        <a href="/services/<?= e($s['slug']) ?>" class="service-card reveal" class="bg-placeholder" style="aspect-ratio: 4/5; display:flex; flex-direction:column; justify-content:flex-end;">
          <div class="eyebrow" style="margin-bottom:1rem;">Service</div>
          <h2 style="font-family:var(--font-display); font-weight:400; font-size:1.75rem; color:var(--color-text-primary); line-height:1.15; margin-bottom:.75rem;"><?= e($s['title']) ?></h2>
          <p class="service-card__desc"><?= e($s['description']) ?></p>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php partial('footer'); ?>
