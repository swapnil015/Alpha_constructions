<?php
partial('head', compact('page_title'));
partial('header');
$testimonials = db_all("SELECT * FROM testimonials WHERE is_active=1 ORDER BY sort_order");
?>
<main id="main">
  <section class="hero" style="min-height: 65vh;">
    <div class="hero__bg"></div>
    <div class="container hero__inner">
      <div class="eyebrow">Why Choose Us</div>
      <h1 class="hero__title" data-split-words>Built to a higher standard</h1>
      <p class="hero__sub reveal">What sets Alpha Concern apart in Nepal's construction landscape.</p>
    </div>
  </section>

  <section class="section section--cream">
    <div class="container">
      <?php $items = [
        ['Decade of Delivery','Over ten years executing residential, commercial, and mixed-use projects across the Kathmandu Valley. Our experience compounds — every project teaches the next.'],
        ['Engineering Rigour','Every structure designed to NBC 105 with independent peer review. We do not cut corners on what holds a building up.'],
        ['Architectural Restraint','We design for longevity. Our buildings still look right ten years on because we resist trend in favour of proportion, light, and material.'],
        ['Transparent Process','Documented schedules, monthly client reporting, and full cost visibility. You always know where your money is and why.'],
        ['In-house Trades','Core trades retained internally for tighter quality control and predictable handover. Less subcontractor risk, fewer surprises.'],
        ['Post-Handover Care','24-month workmanship warranty with responsive maintenance support. Our relationship does not end at handover.'],
      ];
      foreach ($items as $i => $it): ?>
      <div class="split reveal" style="margin-bottom: clamp(3rem, 8vw, 6rem);">
        <div class="split__media bg-placeholder">
          <div style="position:absolute; top:1.5rem; left:1.5rem; font-family:var(--font-display); font-weight:300; font-size:5rem; color:rgba(200,146,42,0.5); line-height:1;"><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?></div>
        </div>
        <div>
          <h2 class="display display-md" style="margin-bottom:1.25rem;"><?= e($it[0]) ?></h2>
          <p><?= e($it[1]) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if ($testimonials): ?>
  <section class="section section--surface">
    <div class="container">
      <div style="text-align:center; margin-bottom:3rem;">
        <div class="eyebrow reveal">Client Voices</div>
        <h2 class="display display-lg reveal" style="margin-top:1rem;">In their own words</h2>
      </div>
      <div class="cards-grid">
        <?php foreach ($testimonials as $t): ?>
        <div class="service-card reveal">
          <span style="font-family:var(--font-display); font-size:3rem; color:var(--color-accent); line-height:.5;">"</span>
          <p style="font-family:var(--font-display); font-style:italic; font-size:1.1rem; color:var(--color-text-primary); margin: 1rem 0;"><?= e($t['review_text']) ?></p>
          <div class="eyebrow" style="margin-top:1rem;"><?= e($t['client_name']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="cta-banner"><div class="cta-banner__bg"></div>
    <div class="container cta-banner__inner">
      <h2 class="display display-lg reveal" style="margin:0 auto 2rem; max-width:20ch;">Let's talk about your project</h2>
      <a href="/contact" class="btn btn-primary reveal">Start a Conversation</a>
    </div>
  </section>
</main>
<?php partial('footer'); ?>
