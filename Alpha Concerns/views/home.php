<?php
$page_description = setting('hero_subheadline');
partial('head', compact('page_title','page_description'));
partial('header');
$projects = db_all("SELECT * FROM projects WHERE is_published=1 AND is_featured=1 ORDER BY sort_order LIMIT 6");
$services = db_all("SELECT * FROM services WHERE is_active=1 ORDER BY sort_order LIMIT 6");
$testimonials = db_all("SELECT * FROM testimonials WHERE is_active=1 ORDER BY sort_order");
$blogs = db_all("SELECT * FROM blog_posts WHERE status='published' ORDER BY published_at DESC LIMIT 3");

// Hero is a scroll-driven frame sequence (assets/js/sequence.js). Loaded only
// here — the engine is dead weight on every other page.
$page_scripts =
    '<script src="' . asset('assets/js/sequence.js') . '" defer></script>' .
    '<script src="' . asset('assets/js/story.js')    . '" defer></script>' .
    '<script src="' . asset('assets/js/services.js') . '" defer></script>';
?>

<main id="main">

  <!-- ==========================================================================
       HERO — scroll-driven frame sequence
       The outer .seq is a tall spacer whose height sequence.js sets to
       (scroll travel + one viewport). The inner .seq__stage pins while the page
       scrolls through that travel, so scroll position scrubs the sequence.
       Caption windows overlap by 0.045 (CONFIG.capFade) to cross-dissolve.
       ========================================================================== -->
  <section class="seq" data-seq aria-label="Alpha Concern — architectural model">
    <div class="seq__stage" data-seq-stage>

      <canvas class="seq__canvas" data-seq-canvas role="img"
              aria-label="A commercial building by Alpha Concern, rotating through a full turn."></canvas>
      <div class="seq__scrim" aria-hidden="true"></div>

      <div class="container seq__content">
        <div class="seq__caps">

          <!-- The hero proper. Copy stays CMS-driven via site_settings. -->
          <div class="seq__cap" data-seq-cap data-in="-0.05" data-out="0.38">
            <h1 class="seq__title"><?= e(setting('hero_headline','Building Tomorrow, Today')) ?></h1>
            <p class="seq__sub"><?= e(setting('hero_subheadline')) ?></p>
            <div class="hero__ctas">
              <a href="/projects" class="btn btn-primary">View Our Projects</a>
              <a href="/contact" class="btn btn-ghost">Free Consultation</a>
            </div>
          </div>

          <div class="seq__cap" data-seq-cap data-in="0.335" data-out="0.72">
            <div class="eyebrow">The Envelope</div>
            <h2 class="seq__title">Timber louvres, tuned to the <span class="italic-accent">sun</span></h2>
            <p class="seq__sub">A vertical brise-soleil cuts western glare while keeping every floorplate daylit.</p>
          </div>

          <div class="seq__cap" data-seq-cap data-in="0.675" data-out="1.05">
            <div class="eyebrow">Engineered for Permanence</div>
            <h2 class="seq__title">Built to <span class="italic-accent">endure</span></h2>
            <p class="seq__sub">NBC 105 seismic resilience, verified by independent peer review.</p>
            <div class="hero__ctas">
              <a href="/contact" class="btn btn-primary">Start Your Project</a>
            </div>
          </div>

        </div>
      </div>

      <!-- Initial keyframe pass. Lifts as soon as the first frame is painted. -->
      <div class="seq__loader" data-seq-loader>
        <div class="seq__loader-track"><span class="seq__loader-bar" data-seq-bar></span></div>
        <div class="seq__loader-pct"><span data-seq-pct>0</span>%</div>
      </div>

      <a href="#stats" class="seq__hint" data-seq-hint aria-label="Scroll down">Scroll</a>
      <div class="seq__rail" aria-hidden="true"><span data-seq-progress></span></div>

    </div>
  </section>

  <!-- STATS -->
  <section class="stats-bar" id="stats">
    <div class="container">
      <div class="stats-grid">
        <div class="stat">
          <div class="stat__num"><span data-count="<?= (int)setting('stat_years',10) ?>">0</span><span class="stat__plus">+</span></div>
          <div class="stat__label">Years of Experience</div>
        </div>
        <div class="stat">
          <div class="stat__num"><span data-count="<?= (int)setting('stat_projects',50) ?>">0</span><span class="stat__plus">+</span></div>
          <div class="stat__label">Projects Completed</div>
        </div>
        <div class="stat">
          <div class="stat__num"><span data-count="<?= (int)setting('stat_clients',200) ?>">0</span><span class="stat__plus">+</span></div>
          <div class="stat__label">Happy Clients</div>
        </div>
        <div class="stat">
          <div class="stat__num"><span data-count="<?= (int)setting('stat_team',80) ?>">0</span><span class="stat__plus">+</span></div>
          <div class="stat__label">Expert Team</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       STORY — pinned editorial scroll section (assets/js/story.js)
       The stage pins for 350vh of scroll while a GSAP timeline, scrubbed by
       scroll position, brings the cards in and drifts them at different depths.
       Markup renders complete and readable without JS; story.js adds .is-live
       and takes over opacity only once GSAP is confirmed present.
       ========================================================================== -->
  <section class="story" data-story aria-label="How Alpha Concern builds">
    <div class="story__stage" data-story-stage>
      <div class="container story__inner">

        <h2 class="story__headline" data-story-headline>
          A building is <em>drawn</em><br>long before it is <em>built</em>.
        </h2>

        <!-- Cards. data-depth drives parallax distance: higher = nearer the
             viewer = travels further. Each slot's aspect-ratio in main.css
             matches its photo's native ratio, so nothing is cropped. -->
        <div class="story__field">
          <figure class="story__card story__card--1" data-story-card data-depth="1">
            <div class="story__cardInner" data-story-inner>
              <img src="<?= asset('assets/img/story/commercial-aerial.jpg') ?>"
                   alt="Aerial view of a commercial block with fluted metal and timber screening."
                   loading="lazy" decoding="async" width="1332" height="1181">
            </div>
          </figure>

          <figure class="story__card story__card--2" data-story-card data-depth="0.4">
            <div class="story__cardInner" data-story-inner>
              <img src="<?= asset('assets/img/story/aion-corner.jpg') ?>"
                   alt="Glazed corner showroom on Jhamsikhel Road, Kathmandu."
                   loading="lazy" decoding="async" width="900" height="1600">
            </div>
          </figure>

          <figure class="story__card story__card--3" data-story-card data-depth="1.3">
            <div class="story__cardInner" data-story-inner>
              <img src="<?= asset('assets/img/story/apartment-dusk.jpg') ?>"
                   alt="Curved residential apartment block at dusk against a wooded hillside."
                   loading="lazy" decoding="async" width="1325" height="881">
            </div>
          </figure>

          <figure class="story__card story__card--4" data-story-card data-depth="0.7">
            <div class="story__cardInner" data-story-inner>
              <img src="<?= asset('assets/img/story/aion-showroom.jpg') ?>"
                   alt="Double-height glazed motor showroom with a car on display."
                   loading="lazy" decoding="async" width="900" height="1600">
            </div>
          </figure>

          <figure class="story__card story__card--5" data-story-card data-depth="1">
            <div class="story__cardInner" data-story-inner>
              <img src="<?= asset('assets/img/story/residence-brick.jpg') ?>"
                   alt="Brick and render private residence with landscaped garden."
                   loading="lazy" decoding="async" width="1600" height="900">
            </div>
          </figure>
        </div>

        <!-- Copy blocks cross-fade, each owning a slice of the timeline. -->
        <div class="story__copy">
          <div class="story__block" data-story-block>
            <div class="story__eyebrow">01 — Design</div>
            <p class="story__text"><?= e(setting('about_snapshot')) ?></p>
          </div>

          <div class="story__block" data-story-block>
            <div class="story__eyebrow">02 — Engineer</div>
            <p class="story__text">Every structure is resolved for NBC 105 seismic performance and put through independent peer review before a foundation is poured.</p>
          </div>

          <div class="story__block" data-story-block>
            <div class="story__eyebrow">03 — Deliver</div>
            <p class="story__text">Core trades stay in house, so quality control and programme sit with one accountable team from ground-breaking to handover.</p>
            <a href="/about" class="story__link">Our Story</a>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ==========================================================================
       SERVICES — pinned, one service per viewport (assets/js/services.js)
       Each .svc__item holds both its text and its image so the same markup can
       stack absolutely on desktop and become a horizontal snap-scroller on
       mobile, where pinning is disabled.
       ========================================================================== -->
  <?php
  // Fallback imagery per service. services.hero_image takes precedence, so this
  // becomes dead weight the moment images are uploaded through the admin panel.
  $svcFallback = [
      'residential'            => 'residence-brick.jpg',
      'commercial'             => 'aion-corner.jpg',
      'real-estate'            => 'apartment-dusk.jpg',
      'interior-design'        => 'aion-showroom.jpg',
      'structural-engineering' => 'commercial-aerial.jpg',
      'project-management'     => 'apartment-facade.jpg',
  ];
  ?>
  <section class="svc" data-svc aria-label="What we do">
    <div class="svc__stage" data-svc-stage>

      <div class="svc__bg" aria-hidden="true">
        <span class="svc__grid"></span>
        <span class="svc__glow"></span>
      </div>

      <div class="svc__eyebrow">What We Do</div>

      <!-- Vertical rail. Frosted glass, hairline gold border. -->
      <nav class="svc__rail" aria-label="Service navigation">
        <span class="svc__railTrack" aria-hidden="true"><span class="svc__railFill" data-svc-railfill></span></span>
        <?php foreach ($services as $i => $s): ?>
        <button type="button" class="svc__railItem<?= $i === 0 ? ' is-active' : '' ?>"
                data-svc-dot="<?= $i ?>" aria-label="<?= e($s['title']) ?>">
          <?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?>
        </button>
        <?php endforeach; ?>
      </nav>

      <div class="svc__items" data-svc-items>
        <?php foreach ($services as $i => $s):
          $img = trim((string)($s['hero_image'] ?? ''));
          $src = $img !== ''
              ? ($img[0] === '/' ? $img : asset(ltrim($img, '/')))
              : asset('assets/img/story/' . ($svcFallback[$s['slug']] ?? 'commercial-aerial.jpg'));
        ?>
        <article class="svc__item<?= $i === 0 ? ' is-active' : '' ?>" data-svc-item="<?= $i ?>">

          <div class="svc__text">
            <div class="svc__num" aria-hidden="true"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></div>
            <h2 class="svc__title"><?= e($s['title']) ?></h2>
            <p class="svc__desc" data-svc-desc><?= e($s['description']) ?></p>
            <a class="svc__cta" href="/services/<?= e($s['slug']) ?>">
              <span>Explore Projects</span>
              <i aria-hidden="true">→</i>
            </a>
          </div>

          <figure class="svc__media" data-svc-media>
            <div class="svc__figure" data-svc-figure>
              <img src="<?= e($src) ?>" alt="<?= e($s['title']) ?> — Alpha Concern project"
                   loading="lazy" decoding="async" width="1600" height="1000">
            </div>
            <span class="svc__vignette" aria-hidden="true"></span>
          </figure>

        </article>
        <?php endforeach; ?>
      </div>

      <a class="svc__all" href="/services">Explore All Services</a>

      <!-- Circular cursor indicator, shown only over the imagery. -->
      <div class="svc__cursor" data-svc-cursor aria-hidden="true"><span>Explore</span></div>

    </div>
  </section>

  <!-- FEATURED PROJECTS -->
  <section class="section section--cream">
    <div class="container">
      <div style="display:flex; justify-content:space-between; align-items:end; flex-wrap:wrap; gap:1rem;">
        <div>
          <div class="eyebrow reveal">Selected Work</div>
          <h2 class="display display-lg reveal" style="margin-top:1rem;">Featured projects</h2>
        </div>
        <a href="/projects" class="btn-text reveal">All Projects</a>
      </div>

      <div data-filter-group>
        <div class="filter-tabs reveal">
          <button class="filter-tab is-active" data-cat="all">All</button>
          <button class="filter-tab" data-cat="Residential">Residential</button>
          <button class="filter-tab" data-cat="Commercial">Commercial</button>
          <button class="filter-tab" data-cat="Mixed-Use">Mixed-Use</button>
        </div>

        <div class="projects-grid">
          <?php foreach ($projects as $p): ?>
          <a href="/projects/<?= e($p['slug']) ?>" class="project-card reveal" data-cat="<?= e($p['type']) ?>">
            <div class="project-card__img" style="background-image: linear-gradient(135deg, rgba(20,18,14,0.35), rgba(20,18,14,0.7)), url('<?= e($p['hero_image']) ?>');"></div>
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
    </div>
  </section>

  <!-- WHY CHOOSE US -->
  <section class="section section--surface">
    <div class="container">
      <div style="text-align:center; max-width:640px; margin:0 auto;">
        <div class="eyebrow reveal">Why Alpha Concern</div>
        <h2 class="display display-lg reveal" style="margin-top:1rem;">Six reasons clients trust us</h2>
      </div>
      <div class="why-grid">
        <?php $items = [
          ['Decade of Delivery','Over ten years executing residential, commercial, and mixed-use projects across the Kathmandu Valley.'],
          ['Engineering Rigour','Every structure designed for NBC 105 seismic resilience with verified peer review.'],
          ['Architectural Restraint','We design for longevity — finishes and lines that age with grace, not trend.'],
          ['Transparent Process','Documented schedules, monthly client reporting, and full cost visibility throughout.'],
          ['In-house Trades','Core trades retained internally for tighter quality control and on-time handover.'],
          ['Post-Handover Care','24-month workmanship warranty with responsive maintenance support.'],
        ];
        foreach ($items as $i => $it): ?>
        <div class="why-item reveal">
          <div class="why-item__num"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?></div>
          <h3 class="why-item__title"><?= e($it[0]) ?></h3>
          <p class="why-item__desc"><?= e($it[1]) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS -->
  <?php if ($testimonials): ?>
  <section class="section section--cream">
    <div class="container-md" data-testimonials>
      <div style="text-align:center; margin-bottom:3rem;">
        <div class="eyebrow reveal">In Their Words</div>
      </div>
      <?php foreach ($testimonials as $t): ?>
      <div class="testimonial reveal">
        <span class="testimonial__mark">"</span>
        <p class="testimonial__text"><?= e($t['review_text']) ?></p>
        <div class="testimonial__name"><?= e($t['client_name']) ?></div>
        <?php if ($t['project_name']): ?><div class="testimonial__project"><?= e($t['project_name']) ?></div><?php endif; ?>
      </div>
      <?php endforeach; ?>
      <div class="testimonial-controls">
        <button data-prev aria-label="Previous testimonial">←</button>
        <button data-next aria-label="Next testimonial">→</button>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- CTA BANNER -->
  <section class="cta-banner">
    <div class="cta-banner__bg"></div>
    <div class="container cta-banner__inner">
      <div class="eyebrow reveal" style="justify-content:center; display:inline-flex;">Let's Build Together</div>
      <h2 class="display display-xl reveal" style="margin: 1.5rem auto; max-width: 16ch;">Ready to build your <span class="italic-accent">dream</span>?</h2>
      <a href="/contact" class="btn btn-primary reveal">Let's Talk</a>
    </div>
  </section>

  <!-- BLOG PREVIEW -->
  <?php if ($blogs): ?>
  <section class="section section--surface">
    <div class="container">
      <div style="display:flex; justify-content:space-between; align-items:end; flex-wrap:wrap; gap:1rem; margin-bottom:3rem;">
        <div>
          <div class="eyebrow reveal">Insights & News</div>
          <h2 class="display display-lg reveal" style="margin-top:1rem;">From the field</h2>
        </div>
        <a href="/blog" class="btn-text reveal">Read All Articles</a>
      </div>
      <div class="cards-grid" style="margin-top:0;">
        <?php foreach ($blogs as $b): ?>
        <a href="/blog/<?= e($b['slug']) ?>" class="service-card reveal" style="aspect-ratio: 4/5; display:flex; flex-direction:column; justify-content:flex-end;">
          <div class="eyebrow" style="margin-bottom:1rem;"><?= e($b['category'] ?: 'Insight') ?></div>
          <h3 class="service-card__title" style="font-family:var(--font-display); font-weight:400; font-size:1.5rem; line-height:1.2;"><?= e($b['title']) ?></h3>
          <div style="margin-top:1rem; font-size:.75rem; color:var(--color-text-muted); letter-spacing:.15em; text-transform:uppercase;">
            <?= fmt_date($b['published_at']) ?> · <?= read_time($b['body']) ?> min read
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

</main>

<?php /* view() extracts into its own scope, so $page_scripts must be passed. */ ?>
<?php partial('footer', compact('page_scripts')); ?>
