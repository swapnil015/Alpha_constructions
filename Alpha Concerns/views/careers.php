<?php
partial('head', compact('page_title'));
partial('header');
$jobs    = db_all("SELECT * FROM job_listings WHERE is_active=1 ORDER BY created_at DESC");
$flashes = flash_get();
?>
<main id="main">
  <section class="hero hero--shot pg-hero">
    <img class="hero--shot__img" src="<?= asset('assets/img/hero/services-prajwal-residency.jpg') ?>"
         alt="A reinforced concrete frame under construction above the Kathmandu valley."
         width="1200" height="1600" fetchpriority="high" decoding="async">
    <span class="hero--shot__scrim" aria-hidden="true"></span>
    <div class="container hero__inner hero--shot__inner">
      <div class="eyebrow">Careers</div>
      <h1 class="hero__title" data-masked>Build with us</h1>
      <p class="hero__sub reveal" data-reveal="up">We hire engineers, designers, and craftspeople who care about the long life of what they build.</p>
    </div>
  </section>

  <section class="section section--cream" x-data="{ open: null }">
    <div class="container-md">
      <?php foreach (($flashes['success'] ?? []) as $m): ?><div class="form-success"><?= e($m) ?></div><?php endforeach; ?>
      <?php foreach (($flashes['error'] ?? []) as $m): ?><div class="form-success" style="border-color:#E8835F;"><?= e($m) ?></div><?php endforeach; ?>

      <div class="cr-intro" style="margin-bottom: clamp(2.5rem, 6vh, 4rem);">
        <div>
          <div class="eyebrow reveal" data-reveal="up">Working Here</div>
          <h2 class="display display-lg reveal" data-reveal="mask" style="margin-top:1rem;"><span>Open positions</span></h2>
        </div>
        <div class="cr-values reveal--stagger">
          <div class="cr-value reveal" data-reveal="up">
            <span class="cr-value__k">01</span>
            <p class="cr-value__v">Core trades are retained in house, so the people who build our projects are our own.</p>
          </div>
          <div class="cr-value reveal" data-reveal="up">
            <span class="cr-value__k">02</span>
            <p class="cr-value__v">Every structure is designed to NBC 105 and put through independent peer review.</p>
          </div>
          <div class="cr-value reveal" data-reveal="up">
            <span class="cr-value__k">03</span>
            <p class="cr-value__v">Work spans residential, commercial and institutional projects across the valley.</p>
          </div>
        </div>
      </div>

      <?php if (!$jobs): ?>
        <div class="cr-empty reveal" data-reveal="up">
          <p style="margin:0 0 .75rem;">No open positions at the moment.</p>
          <p style="margin:0;">We welcome speculative applications at
            <a href="mailto:<?= e(setting('email_primary')) ?>" style="color:var(--color-accent);"><?= e(setting('email_primary')) ?></a>.
          </p>
        </div>
      <?php endif; ?>

      <div class="reveal--stagger">
      <?php foreach ($jobs as $j): ?>
      <div class="cr-job reveal" data-reveal="up" :class="{ 'is-open': open === <?= (int)$j['id'] ?> }">
        <button class="cr-job__btn" @click="open === <?= (int)$j['id'] ?> ? open = null : open = <?= (int)$j['id'] ?>"
                :aria-expanded="open === <?= (int)$j['id'] ?>">
          <div>
            <div class="eyebrow" style="margin-bottom:.5rem;"><?= e($j['department']) ?> · <?= e($j['employment_type']) ?> · <?= e($j['location']) ?></div>
            <h3 class="cr-job__title"><?= e($j['title']) ?></h3>
          </div>
          <span class="cr-job__sign" aria-hidden="true"></span>
        </button>
        <div x-show="open === <?= (int)$j['id'] ?>" x-collapse style="padding: 0 1.75rem 2rem;">
          <div style="border-top:1px solid var(--color-border); padding-top:1.5rem;">
            <h4 class="eyebrow" style="margin-bottom:.75rem;">Description</h4>
            <div><?= $j['description'] ?></div>
            <h4 class="eyebrow" style="margin: 1.5rem 0 .75rem;">Requirements</h4>
            <div><?= $j['requirements'] ?></div>

            <form action="/api/career.php" method="post" enctype="multipart/form-data" style="margin-top:2rem;">
              <?= csrf_field() ?>
              <input type="hidden" name="job_id" value="<?= (int)$j['id'] ?>">
              <input type="text" name="website" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
              <div class="split" style="grid-template-columns: 1fr 1fr;">
                <div class="form-group"><label class="form-label">Full Name *</label><input class="form-control" name="applicant_name" required maxlength="150"></div>
                <div class="form-group"><label class="form-label">Email *</label><input class="form-control" type="email" name="email" required maxlength="200"></div>
                <div class="form-group"><label class="form-label">Phone</label><input class="form-control" name="phone" maxlength="30"></div>
                <div class="form-group"><label class="form-label">CV (PDF/DOC, max 5MB) *</label><input class="form-control" type="file" name="cv" accept=".pdf,.doc,.docx" required></div>
              </div>
              <div class="form-group"><label class="form-label">Cover Note</label><textarea class="form-control" name="cover_note" maxlength="2000"></textarea></div>
              <button class="btn btn-primary is-magnetic" type="submit">Submit Application</button>
            </form>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php partial('footer'); ?>
