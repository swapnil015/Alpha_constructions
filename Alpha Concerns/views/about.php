<?php
partial('head', compact('page_title'));
partial('header');
$team = db_all("SELECT * FROM team_members WHERE is_active=1 ORDER BY sort_order");

// Motion only — see assets/js/about.js. Nothing below changes the page's
// colours, type, layout or spacing; the hooks are data attributes and classes
// that carry no visual style of their own at rest.
$page_scripts = '<script src="' . asset('assets/js/about.js') . '" defer></script>';
?>
<main id="main" data-about>
  <section class="hero" style="min-height: 70vh;">
    <div class="hero__bg"></div>
    <div class="container hero__inner" data-about-hero>
      <div class="eyebrow">About Alpha Concern</div>
      <h1 class="hero__title" data-about-hero-title>Who We Are</h1>
      <p class="hero__sub" data-about-hero-sub>A construction and real-estate development practice founded on engineering rigour and architectural restraint.</p>
    </div>
  </section>

  <section class="section section--cream">
    <div class="container split">
      <div class="split__media bg-placeholder" data-about-media><span class="split__media-inner"></span></div>
      <div data-about-story-copy>
        <div class="eyebrow">Our Story</div>
        <h2 class="display display-lg" data-about-story-title style="margin:1rem 0 1.5rem;">A decade of building <span class="italic-accent">that endures</span></h2>
        <p style="margin-bottom:1rem;"><?= e(setting('about_snapshot')) ?></p>
        <p>Founded in Kathmandu, we have spent over a decade delivering residences, commercial spaces, and mixed-use developments. Our work is defined by what endures: structural integrity, considered detailing, and the kind of craftsmanship that ages well.</p>
      </div>
    </div>
  </section>

  <section class="section section--surface">
    <div class="container">
      <div class="cards-grid" data-about-cards style="margin-top:0;">
        <div class="service-card is-traced">
          <div class="eyebrow" style="margin-bottom:1rem;">Mission</div>
          <p>To build homes and commercial spaces that meet the highest standards of structural integrity, architectural quality, and operational reliability.</p>
        </div>
        <div class="service-card is-traced">
          <div class="eyebrow" style="margin-bottom:1rem;">Vision</div>
          <p>To be Nepal's most trusted name in premium construction — recognised for craft, engineering, and the long life of our buildings.</p>
        </div>
        <div class="service-card is-traced">
          <div class="eyebrow" style="margin-bottom:1rem;">Values</div>
          <p>Integrity in materials. Discipline in process. Restraint in design. Care for the people who will live and work in what we build.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="stats-bar">
    <div class="container">
      <div class="stats-grid" data-about-stats>
        <div class="stat"><div class="stat__num"><span data-countup="<?= (int)setting('stat_years',10) ?>">0</span><span class="stat__plus">+</span></div><div class="stat__label">Years</div></div>
        <div class="stat"><div class="stat__num"><span data-countup="<?= (int)setting('stat_projects',50) ?>">0</span><span class="stat__plus">+</span></div><div class="stat__label">Projects</div></div>
        <div class="stat"><div class="stat__num"><span data-countup="<?= (int)setting('stat_clients',200) ?>">0</span><span class="stat__plus">+</span></div><div class="stat__label">Clients</div></div>
        <div class="stat"><div class="stat__num"><span data-countup="<?= (int)setting('stat_team',80) ?>">0</span><span class="stat__plus">+</span></div><div class="stat__label">Team</div></div>
      </div>
    </div>
  </section>

  <?php if ($team): ?>
  <section class="section section--cream">
    <div class="container">
      <div style="text-align:center; max-width:640px; margin:0 auto 3rem;">
        <div class="eyebrow">Leadership</div>
        <h2 class="display display-lg" data-about-team-title style="margin-top:1rem;">The team behind every build</h2>
      </div>
      <div class="cards-grid" data-about-cards>
        <?php foreach ($team as $m): ?>
        <div class="service-card is-traced is-person">
          <div class="bg-placeholder person__media" style="aspect-ratio:1; margin-bottom:1.5rem;"><span class="person__img"></span></div>
          <div class="eyebrow person__role" style="margin-bottom:.5rem;"><?= e($m['title']) ?></div>
          <h3 class="service-card__title person__name"><?= e($m['name']) ?></h3>
          <p class="service-card__desc"><?= e($m['bio']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="section section--surface about-process">
    <span class="about-process__rail" aria-hidden="true"><span data-about-rail></span></span>
    <div class="container">
      <div style="text-align:center; max-width:640px; margin:0 auto 3rem;">
        <div class="eyebrow">Our Process</div>
        <h2 class="display display-lg" data-about-process-title style="margin-top:1rem;">From first conversation to final handover</h2>
      </div>
      <div class="cards-grid" data-about-process>
        <?php foreach ([['01','Consultation','We listen, scope, and align on goals.'],['02','Design','Concept through construction documents.'],['03','Approval','Permits, stakeholders, and final sign-off.'],['04','Construction','Disciplined execution with full transparency.'],['05','Handover','Quality assurance, snagging, and warranty.'],['06','Care','Post-handover support and long-term maintenance.']] as $st): ?>
        <div class="service-card is-step">
          <div class="why-item__num"><?= $st[0] ?></div>
          <h3 class="service-card__title"><?= $st[1] ?></h3>
          <p class="service-card__desc"><?= $st[2] ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="cta-banner"><div class="cta-banner__bg"></div>
    <div class="container cta-banner__inner">
      <h2 class="display display-lg reveal" style="margin:0 auto 2rem; max-width:20ch;">Begin a conversation</h2>
      <a href="/contact" class="btn btn-primary reveal">Contact Us</a>
    </div>
  </section>
</main>
<?php partial('footer', compact('page_scripts')); ?>
