<?php
partial('head', compact('page_title'));
partial('header');
$testimonials = db_all("SELECT * FROM testimonials WHERE is_active=1 ORDER BY sort_order");

$page_scripts = '<script src="' . asset('assets/js/why-us.js') . '" defer></script>';

/*
 * The six reasons. Each carries a line-drawn icon rather than the empty
 * .bg-placeholder box this page used to show beside every block — there is no
 * photography for this section, and an undrawn grey rectangle reads as
 * unfinished. Icons are stroked in gold at hairline weight and draw themselves
 * on reveal (see why-us.js).
 */
$reasons = [
    [
        'title' => 'Decade of Delivery',
        'body'  => 'Over ten years executing residential, commercial, and mixed-use projects across the Kathmandu Valley. Our experience compounds — every project teaches the next.',
        'icon'  => '<path d="M8 56V22l14-8v42"/><path d="M22 56V30l18-8v34"/><path d="M40 56V34l14 6v16"/><path d="M4 56h56"/><path d="M13 28v4M17 28v4M13 38v4M17 38v4M27 34v4M32 34v4M27 44v4M32 44v4M45 42v4M49 42v4"/>',
    ],
    [
        'title' => 'Engineering Rigour',
        'body'  => 'Every structure designed to NBC 105 with independent peer review. We do not cut corners on what holds a building up.',
        'icon'  => '<path d="M6 44h52"/><path d="M10 44V20h44v24"/><path d="M10 20l22 14 22-14"/><path d="M32 34v10"/><path d="M18 52v6M46 52v6"/><path d="M14 44l8 8M50 44l-8 8"/>',
    ],
    [
        'title' => 'Architectural Restraint',
        'body'  => 'We design for longevity. Our buildings still look right ten years on because we resist trend in favour of proportion, light, and material.',
        'icon'  => '<rect x="8" y="8" width="48" height="48"/><path d="M8 26h48M8 40h48M26 8v48M40 8v48"/><path d="M8 8l48 48"/>',
    ],
    [
        'title' => 'Transparent Process',
        'body'  => 'Documented schedules, monthly client reporting, and full cost visibility. You always know where your money is and why.',
        'icon'  => '<path d="M14 6h26l10 10v42H14z"/><path d="M40 6v10h10"/><path d="M21 28h22M21 36h22M21 44h14"/>',
    ],
    [
        'title' => 'In-house Trades',
        'body'  => 'Core trades retained internally for tighter quality control and predictable handover. Less subcontractor risk, fewer surprises.',
        'icon'  => '<path d="M34 10l18 6-6 16-18-6z"/><path d="M28 26L10 44a5 5 0 007 7l18-18"/><path d="M40 34l12 16"/>',
    ],
    [
        'title' => 'Post-Handover Care',
        'body'  => '24-month workmanship warranty with responsive maintenance support. Our relationship does not end at handover.',
        'icon'  => '<path d="M32 6l22 8v16c0 14-9 22-22 26-13-4-22-12-22-26V14z"/><path d="M23 32l6 6 12-12"/>',
    ],
];
?>
<main id="main" data-why-us>

  <!-- ==========================================================================
       HERO — two deliberate lines, credibility stats filling the right half
       ========================================================================== -->
  <section class="hero wy-hero">
    <div class="hero__bg"></div>
    <div class="container hero__inner">
      <div class="wy-hero__grid">
        <div data-wy-heroblock>
          <div class="eyebrow">Why Choose Us</div>

          <h1 class="hero__title" data-wy-title>
            <span class="split-line"><span>Built to a</span></span>
            <span class="split-line"><span>higher standard</span></span>
          </h1>

          <p class="hero__sub" data-wy-sub>What sets Alpha Concern apart in Nepal's construction landscape.</p>
        </div>

        <!-- Dead space turned into proof. -->
        <div class="wy-stats" data-wy-stats>
          <div class="wy-stat">
            <div class="wy-stat__num" data-wy-stat>NBC 105</div>
            <div class="wy-stat__label">Compliant structures</div>
          </div>
          <div class="wy-stat">
            <div class="wy-stat__num" data-wy-stat><span data-wy-count="24">0</span> mo</div>
            <div class="wy-stat__label">Workmanship warranty</div>
          </div>
          <div class="wy-stat">
            <div class="wy-stat__num" data-wy-stat><span data-wy-count="10">0</span>+ yrs</div>
            <div class="wy-stat__label">In the valley</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       REASONS — sticky index beside scrolling blocks
       ========================================================================== -->
  <section class="section section--cream wy-reasons" data-wy-reasons>
    <div class="container">
      <div class="wy-reasons__grid">

        <aside class="wy-index">
          <div class="wy-index__head">
            <div class="eyebrow reveal" data-reveal="up">Why Choose Us</div>
            <h2 class="wy-index__title reveal" data-reveal="up" data-reveal-delay="0.1">Six reasons clients stay with us.</h2>
          </div>

          <nav class="wy-index__list" aria-label="Reasons">
            <span class="wy-rail" aria-hidden="true"><span class="wy-rail__fill" data-wy-rail></span></span>
            <?php foreach ($reasons as $i => $r): ?>
            <a class="wy-index__item<?= $i === 0 ? ' is-active' : '' ?>" href="#reason-<?= $i + 1 ?>" data-wy-idx="<?= $i ?>">
              <span class="wy-index__num"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
              <span class="wy-index__name"><?= e($r['title']) ?></span>
            </a>
            <?php endforeach; ?>
          </nav>
        </aside>

        <div class="wy-blocks">
          <?php foreach ($reasons as $i => $r): ?>
          <article class="wy-block" id="reason-<?= $i + 1 ?>" data-wy-block="<?= $i ?>">
            <div class="wy-block__num reveal" data-reveal="blur"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></div>
            <div>
              <svg class="wy-icon reveal" data-reveal="up" data-wy-icon viewBox="0 0 64 64" aria-hidden="true"><?= $r['icon'] ?></svg>
              <h3 class="wy-block__title reveal" data-reveal="mask"><span><?= e($r['title']) ?></span></h3>
              <p class="wy-block__body reveal" data-reveal="up" data-reveal-delay="0.1"><?= e($r['body']) ?></p>
            </div>
          </article>
          <?php endforeach; ?>
        </div>

      </div>
    </div>
  </section>

  <?php if ($testimonials): ?>
  <section class="section section--surface wy-voices">
    <div class="container">
      <div style="text-align:center; margin-bottom:3rem;">
        <div class="eyebrow reveal" data-reveal="up">Client Voices</div>
        <h2 class="display display-lg reveal" data-reveal="mask" style="margin-top:1rem;"><span>In their own words</span></h2>
      </div>
      <div class="cards-grid reveal--stagger">
        <?php foreach ($testimonials as $t): ?>
        <div class="service-card wy-quote reveal" data-reveal="up">
          <span class="wy-quote__mark" aria-hidden="true">&ldquo;</span>
          <p style="font-family:var(--font-display); font-style:italic; font-size:1.1rem; color:var(--color-text-primary); margin: 1rem 0;"><?= e($t['review_text']) ?></p>
          <div class="wy-quote__attr">
            <div class="eyebrow"><?= e($t['client_name']) ?></div>
            <?php if (!empty($t['project_name'])): ?>
            <span class="wy-quote__meta"><?= e($t['project_name']) ?></span>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="cta-banner"><div class="cta-banner__bg"></div>
    <div class="container cta-banner__inner">
      <h2 class="display display-lg reveal" data-reveal="mask" style="margin:0 auto 2rem; max-width:20ch;"><span>Let's talk about your project</span></h2>
      <a href="/contact" class="btn btn-primary reveal" data-reveal="up" data-reveal-delay="0.2" data-wy-cta>Start a Conversation</a>
    </div>
  </section>
</main>
<?php partial('footer', compact('page_scripts')); ?>
