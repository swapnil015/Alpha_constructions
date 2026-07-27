<?php
partial('head', compact('page_title'));
partial('header');
$services = db_all("SELECT * FROM services WHERE is_active=1 ORDER BY sort_order");
?>
<main id="main">
  <!-- Split hero: copy left, graded site photograph bleeding off the right. -->
  <section class="hero hero--split">
    <div class="hero__bg"></div>
    <div class="hero--split__inner">
      <div class="hero--split__copy">
        <div class="eyebrow">Services</div>
        <h1 class="hero__title" data-masked>What we build</h1>
        <p class="hero__sub reveal">A complete construction practice — from structural engineering to interior finishing.</p>
        <a class="hero--split__scroll" href="#services-list">Scroll to explore</a>
      </div>
      <figure class="hero--split__media">
        <img src="<?= asset('assets/img/hero/services-prajwal-residency.jpg') ?>"
             alt="Prajwal Residency under construction — RCC frame above the Kathmandu valley."
             width="1200" height="1600" fetchpriority="high" decoding="async">
        <span class="hero--split__grade" aria-hidden="true"></span>
        <span class="hero--split__scrim" aria-hidden="true"></span>
        <span class="hero--split__frame" aria-hidden="true"></span>
      </figure>
    </div>
  </section>
  <section class="section section--cream" id="services-list">
    <div class="container">
      <div class="cards-grid" style="margin-top:0;">
        <?php foreach ($services as $s): ?>
        <a href="/services/<?= e($s['slug']) ?>" class="service-card reveal bg-placeholder" style="aspect-ratio: 4/5; display:flex; flex-direction:column; justify-content:flex-end;">
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
