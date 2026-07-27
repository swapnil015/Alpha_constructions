<?php
$page_description = setting('hero_subheadline');
partial('head', compact('page_title','page_description'));
partial('header');
$projects = db_all("SELECT * FROM projects WHERE is_published=1 AND is_featured=1 ORDER BY sort_order LIMIT 6");
$services = db_all("SELECT * FROM services WHERE is_active=1 ORDER BY sort_order LIMIT 6");

// Hero is a scroll-driven frame sequence (assets/js/sequence.js). Loaded only
// here — the engine is dead weight on every other page.
$page_scripts =
    '<script src="' . asset('assets/js/sequence.js') . '" defer></script>' .
    '<script src="' . asset('assets/js/story.js')    . '" defer></script>' .
    '<script src="' . asset('assets/js/services.js') . '" defer></script>' .
    '<script src="' . asset('assets/js/projects.js') . '" defer></script>' .
    '<script src="' . asset('assets/js/why.js')      . '" defer></script>';
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
          <?php if (setting('story_headline')): ?>
            <?= e(setting('story_headline')) ?>
          <?php else: ?>
            A building is <em>drawn</em><br>long before it is <em>built</em>.
          <?php endif; ?>
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

        <!-- Copy blocks cross-fade, each owning a slice of the timeline.
             Editable in /admin/homepage; defaults below are the fallback. -->
        <?php $storyDefaults = [
            1 => ['01 — Design',   setting('about_snapshot')],
            2 => ['02 — Engineer', 'Every structure is resolved for NBC 105 seismic performance and put through independent peer review before a foundation is poured.'],
            3 => ['03 — Deliver',  'Core trades stay in house, so quality control and programme sit with one accountable team from ground-breaking to handover.'],
        ]; ?>
        <div class="story__copy">
          <?php foreach ($storyDefaults as $i => [$eyebrow, $text]): ?>
          <div class="story__block" data-story-block>
            <div class="story__eyebrow"><?= e(setting("story_eyebrow_$i", $eyebrow)) ?></div>
            <p class="story__text"><?= e(setting("story_text_$i", $text)) ?></p>
            <?php if ($i === 3): ?><a href="/about" class="story__link">Our Story</a><?php endif; ?>
          </div>
          <?php endforeach; ?>
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

  // Where each title breaks across its two lines (second line is set in gold).
  // Hand-authored because an automatic split reads badly on several of these.
  $svcSplit = [
      'residential'            => ['Residential', 'Construction'],
      'commercial'             => ['Commercial',  'Construction'],
      'real-estate'            => ['Real Estate', 'Development'],
      // "Design & Finishing" is too long to hold one line at the title size —
      // it would wrap to a third line and shift the whole block.
      'interior-design'        => ['Interior Design', '& Finishing'],
      'structural-engineering' => ['Structural',  'Engineering'],
      'project-management'     => ['Project',     'Management'],
  ];

  /*
   * Per-service figures for the stat row.
   *
   * These are drawn from the site-wide numbers in site_settings, which are the
   * only verified figures available — so every service currently shows the same
   * three. Genuinely per-service metrics need real numbers from the client;
   * there is no services table column for them yet. Do not invent any.
   */
  $svcStats = [
      ['icon' => 'home',  'value' => (int)setting('stat_projects', 50) . '+', 'label' => 'Projects Completed'],
      ['icon' => 'plan',  'value' => (int)setting('stat_clients', 200) . '+', 'label' => 'Happy Clients'],
      ['icon' => 'award', 'value' => (int)setting('stat_years', 10) . '+',    'label' => 'Years Experience'],
  ];

  // Minimal line icons, matching the hairline weight of the rest of the section.
  $svcIcons = [
      'home'  => '<path d="M3 11.5 12 4l9 7.5V21h-6v-6h-6v6H3z"/>',
      'plan'  => '<path d="M4 4h16v16H4z"/><path d="M4 9h5v6H4z"/><path d="M13 4v6h7"/>',
      'award' => '<circle cx="12" cy="9" r="5"/><path d="M9 13.5 7.5 21l4.5-2.5L16.5 21 15 13.5"/>',
  ];
  ?>
  <section class="svc" data-svc aria-label="What we do">
    <div class="svc__stage" data-svc-stage>

      <div class="svc__bg" aria-hidden="true">
        <span class="svc__grid"></span>
        <span class="svc__glow"></span>
      </div>

      <!-- Hairline the rail sits against, full stage height. -->
      <span class="svc__hairline" aria-hidden="true"></span>

      <!-- Vertical index. Active number turns gold and grows a lead line. -->
      <nav class="svc__rail" aria-label="Service navigation">
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

          // Fall back to splitting after the first word if the slug is unknown.
          $parts = $svcSplit[$s['slug']] ?? (function (string $t): array {
              $p = explode(' ', $t, 2);
              return [$p[0], $p[1] ?? ''];
          })($s['title']);
        ?>
        <article class="svc__item<?= $i === 0 ? ' is-active' : '' ?>" data-svc-item="<?= $i ?>">

          <div class="svc__text">
            <div class="svc__eyebrow">What We Do<span aria-hidden="true"></span></div>

            <h2 class="svc__title">
              <span class="svc__titleTop"><?= e($parts[0]) ?></span>
              <?php if ($parts[1] !== ''): ?><span class="svc__titleBot"><?= e($parts[1]) ?></span><?php endif; ?>
            </h2>

            <p class="svc__desc" data-svc-desc><?= e($s['description']) ?></p>

            <span class="svc__rule" aria-hidden="true"></span>

            <ul class="svc__stats">
              <?php foreach ($svcStats as $stat): ?>
              <li class="svc__stat">
                <svg class="svc__statIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <?= $svcIcons[$stat['icon']] ?>
                </svg>
                <span class="svc__statValue"><?= e($stat['value']) ?></span>
                <span class="svc__statLabel"><?= e($stat['label']) ?></span>
              </li>
              <?php endforeach; ?>
            </ul>

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

      <!-- Step controls: vertical bottom-left, horizontal over the image. -->
      <div class="svc__nav svc__nav--v">
        <button type="button" data-svc-prev aria-label="Previous service">&#8593;</button>
        <button type="button" data-svc-next aria-label="Next service">&#8595;</button>
      </div>
      <div class="svc__nav svc__nav--h">
        <button type="button" data-svc-prev aria-label="Previous service">&#8592;</button>
        <button type="button" data-svc-next aria-label="Next service">&#8594;</button>
      </div>

      <!-- Circular cursor indicator, shown only over the imagery. -->
      <div class="svc__cursor" data-svc-cursor aria-hidden="true"><span>Explore</span></div>

    </div>
  </section>

  <!-- ==========================================================================
       CURRENT PROJECTS — two cinematic panels (assets/js/projects.js)
       Panels are sticky and stack: the second rises over the first, which dims
       and settles back, so the two read as one continuous move rather than two
       independent cards.
       ========================================================================== -->
  <?php
  /*
   * Exactly two panels, defined here rather than pulled from the projects
   * table: Budhanilkantha Heights is not a row in that table yet, and this
   * section is deliberately a curated pair rather than "whatever is featured".
   *
   * `video` is preferred; `poster` doubles as the still fallback for a
   * project with no film.
   */
  $cpDefaults = [
      1 => [
          'slug'     => 'imperial-apartment',
          'name'     => 'Imperial Apartment',
          'category' => 'Residential',
          'location' => 'Naxal, Kathmandu',
          'status'   => 'Ongoing',
          'video'    => asset('assets/video/imperial-apartment.mp4'),
          'poster'   => asset('assets/video/imperial-apartment.jpg'),
      ],
      2 => [
          'slug'     => 'budhanilkantha-heights',
          'name'     => 'Budhanilkantha Heights',
          'category' => 'Residential',
          'location' => 'Budhanilkantha, Kathmandu',
          'status'   => 'Ongoing',
          'video'    => asset('assets/video/budhanilkantha-heights.mp4'),
          'poster'   => asset('assets/video/budhanilkantha-heights.jpg'),
          // This film carries burned-in captions along its bottom edge; the
          // .is-film CSS lifts the overlay clear of them.
          'captioned' => true,
      ],
  ];
  // Every field is editable in /admin/homepage; empty settings keep the defaults.
  $currentProjects = [];
  foreach ($cpDefaults as $n => $d) {
      $currentProjects[] = [
          'slug'     => setting("cp{$n}_slug",     $d['slug']),
          'name'     => setting("cp{$n}_name",     $d['name']),
          'category' => setting("cp{$n}_category", $d['category']),
          'location' => setting("cp{$n}_location", $d['location']),
          'status'   => setting("cp{$n}_status",   $d['status']),
          'video'    => setting("cp{$n}_video")    ?: $d['video'],
          'poster'   => setting("cp{$n}_poster")   ?: $d['poster'],
          'captioned' => !empty($d['captioned']),
      ];
  }
  ?>
  <section class="cp" data-cp aria-label="Current projects">

    <div class="cp__bg" aria-hidden="true"><span class="cp__contours"></span></div>

    <header class="cp__head">
      <div class="cp__eyebrow">Current Projects</div>
      <h2 class="cp__title" data-cp-title>Current Projects</h2>
      <p class="cp__lede">Two developments on site now — followed from first drawing to handover.</p>
    </header>

    <div class="cp__panels">
      <?php foreach ($currentProjects as $i => $p): ?>
      <article class="cp__panel<?= $p['captioned'] ? ' is-film' : '' ?>" data-cp-panel="<?= $i ?>">

        <div class="cp__media">
          <div class="cp__zoom">
            <?php if ($p['video']): ?>
              <!-- src is set by projects.js once the panel nears the viewport,
                   so the file is never fetched for visitors who never reach it. -->
              <video class="cp__video" data-cp-video
                     data-src="<?= e($p['video']) ?>"
                     poster="<?= e($p['poster']) ?>"
                     muted loop playsinline preload="metadata"
                     aria-label="<?= e($p['name']) ?> film"></video>
            <?php else: ?>
              <img class="cp__still" src="<?= e($p['poster']) ?>"
                   alt="<?= e($p['name']) ?>, <?= e($p['location']) ?>"
                   loading="lazy" decoding="async" width="1600" height="900">
            <?php endif; ?>
          </div>
          <span class="cp__scrim" aria-hidden="true"></span>
        </div>

        <span class="cp__badge"><?= e($p['status']) ?></span>

        <div class="cp__meta">
          <div class="cp__cat" data-cp-cat><?= e($p['category']) ?></div>
          <h3 class="cp__name" data-cp-name><?= e($p['name']) ?></h3>
          <div class="cp__loc" data-cp-loc><?= e($p['location']) ?></div>
        </div>

        <a class="cp__cta" href="/projects/<?= e($p['slug']) ?>">
          <span>Explore Project</span><i aria-hidden="true">→</i>
        </a>

        <span class="cp__divider" data-cp-divider aria-hidden="true"></span>
      </article>
      <?php endforeach; ?>
    </div>

    <a class="cp__all" href="/projects">All Projects</a>
  </section>

  <!-- ==========================================================================
       WHY ALPHA CONCERN — staggered glass timeline (assets/js/why.js)
       Six cards alternating left/right along a gold line that draws itself as
       the section is scrolled, with a dot travelling ahead of the fill.
       ========================================================================== -->
  <?php
  /*
   * Illustrative outline icons, drawn on a 48-unit grid so they carry detail at
   * the size the cards render them (56px) — a 24-unit glyph looks thin and
   * under-drawn that large.
   */
  $whyIcons = [
      'tower' =>
          '<path d="M6 44V16l11-5v33"/><path d="M17 44V21l14-6v29"/><path d="M31 44V25l11 5v14"/>'.
          '<path d="M2 44h44"/>'.
          '<path d="M9.5 20v3M13 18.5v3M9.5 27v3M13 27v3M9.5 34v3M13 34v3"/>'.
          '<path d="M21 25v3M25 25v3M21 32v3M25 32v3M21 39v3M25 39v3"/>'.
          '<path d="M34.5 31v3M38 31v3M34.5 38v3M38 38v3"/>',
      'blueprint' =>
          '<path d="M10 8h30a4 4 0 0 1 0 8H10z"/><path d="M10 8a4 4 0 0 0 0 8"/>'.
          '<path d="M12 16v24h28V16"/><path d="M12 40a3 3 0 0 0 3 3h28a3 3 0 0 1-3-3"/>'.
          '<path d="M17 22h9v8h-9z"/><path d="M30 22h6M30 27h6M30 32h6"/><path d="M17 35h19"/>',
      'compass' =>
          '<circle cx="24" cy="9" r="3.5"/><path d="M22.5 12 14 41"/><path d="M25.5 12 34 41"/>'.
          '<path d="m14 41-3 4 4-1"/><path d="m34 41 3 4-4-1"/>'.
          '<path d="M18.6 27h10.8"/><path d="M8 33h32"/><path d="M12 30v6M36 30v6"/>',
      'handshake' =>
          '<path d="M24 18 19 14a4 4 0 0 0-5.6.5L6 23l6 6 4-4"/>'.
          '<path d="m24 18 5-4a4 4 0 0 1 5.6.5L42 23l-6 6-4-4"/>'.
          '<path d="m16 25 6 6M20 22l7 7M26 30l4 4"/>'.
          '<path d="M12 29c-2 2-2 4 0 6l7 6c2 2 4 2 6 0l11-12"/>',
      'tools' =>
          '<path d="M30 12a7 7 0 0 0 9.2 9.2L42 24 24 42l-6-6L36 18z"/>'.
          '<path d="M18 12 12 6 6 12l6 6"/><path d="m12 18 18 18"/>'.
          '<path d="M6 36a4 4 0 0 1 6 6l-3 3-6-6z"/>',
      'shield' =>
          '<path d="M24 5 41 11v13c0 10-7 16-17 19-10-3-17-9-17-19V11z"/>'.
          '<path d="m17 24 5 5 10-10"/>',
  ];

  $whyDefaults = [
      ['tower',     'Decade of Delivery',      'Over ten years executing residential, commercial, and mixed-use projects across the Kathmandu Valley.'],
      ['blueprint', 'Engineering Rigour',      'Every structure designed for NBC 105 seismic resilience with verified peer review.'],
      ['compass',   'Architectural Restraint', 'We design for longevity — finishes and lines that age with grace, not trend.'],
      ['handshake', 'Transparent Process',     'Documented schedules, monthly client reporting, and full cost visibility throughout.'],
      ['tools',     'In-house Trades',         'Core trades retained internally for tighter quality control and on-time handover.'],
      ['shield',    'Post-Handover Care',      '24-month workmanship warranty with responsive maintenance support.'],
  ];
  // Titles and text editable in /admin/homepage; icons stay with the slot.
  $whyItems = [];
  foreach ($whyDefaults as $i => [$icon, $title, $desc]) {
      $n = $i + 1;
      $whyItems[] = [$icon, setting("why{$n}_title", $title), setting("why{$n}_desc", $desc)];
  }
  ?>
  <section class="why" data-why aria-label="Why Alpha Concern">

    <div class="why__bg" aria-hidden="true">
      <span class="why__blueprint"></span>
      <span class="why__glow"></span>
    </div>

    <header class="why__head">
      <div class="why__eyebrow">Why Alpha Concern</div>
      <h2 class="why__title" data-why-title>Why Clients Choose Alpha Concern</h2>
      <p class="why__statement">Built on precision, trusted through execution.</p>
    </header>

    <div class="why__timeline">
      <!-- Stepped connector. The path is generated by why.js from the cards'
           real positions, so it re-routes correctly at every breakpoint. -->
      <svg class="why__wire" data-why-wire aria-hidden="true" preserveAspectRatio="none">
        <path class="why__wireTrack" data-why-track fill="none"></path>
        <path class="why__wireFill"  data-why-fill  fill="none"></path>
        <path class="why__wireSpark" data-why-spark fill="none"></path>
        <g class="why__joints" data-why-joints></g>
      </svg>

      <ol class="why__list">
        <?php foreach ($whyItems as $i => $it): ?>
        <li class="why__row why__row--<?= $i % 2 === 0 ? 'left' : 'right' ?>" data-why-row="<?= $i ?>">
          <span class="why__num" aria-hidden="true"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>

          <article class="why__card" data-why-card>
            <svg class="why__icon" viewBox="0 0 48 48" fill="none" stroke="currentColor"
                 stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <?= $whyIcons[$it[0]] ?>
            </svg>
            <div class="why__copy">
              <h3 class="why__cardTitle"><?= e($it[1]) ?></h3>
              <p class="why__cardDesc"><?= e($it[2]) ?></p>
            </div>
          </article>
        </li>
        <?php endforeach; ?>
      </ol>
    </div>
  </section>

  <!-- CTA BANNER -->
  <section class="cta-banner">
    <div class="cta-banner__bg"></div>
    <div class="container cta-banner__inner">
      <div class="eyebrow reveal" style="justify-content:center; display:inline-flex;">Let's Build Together</div>
      <h2 class="display display-xl reveal" style="margin: 1.5rem auto; max-width: 16ch;">Ready to build your <span class="italic-accent">dream</span>?</h2>
      <a href="/contact" class="btn btn-primary reveal">Let's Talk</a>
    </div>
  </section>

</main>

<?php /* view() extracts into its own scope, so $page_scripts must be passed. */ ?>
<?php partial('footer', compact('page_scripts')); ?>
