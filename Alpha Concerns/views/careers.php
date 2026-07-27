<?php
partial('head', compact('page_title'));
partial('header');
$jobs    = db_all("SELECT * FROM job_listings WHERE is_active=1 ORDER BY created_at DESC");
$flashes = flash_get();
?>
<main id="main">
  <section class="hero" style="min-height: 55vh;">
    <div class="hero__bg"></div>
    <div class="container hero__inner">
      <div class="eyebrow">Careers</div>
      <h1 class="hero__title" data-split-words>Build with us</h1>
      <p class="hero__sub reveal">We hire engineers, designers, and craftspeople who care about the long life of what they build.</p>
    </div>
  </section>

  <section class="section section--cream" x-data="{ open: null }">
    <div class="container-md">
      <?php foreach (($flashes['success'] ?? []) as $m): ?><div class="form-success"><?= e($m) ?></div><?php endforeach; ?>
      <?php foreach (($flashes['error'] ?? []) as $m): ?><div class="form-success" style="border-color:#E8835F;"><?= e($m) ?></div><?php endforeach; ?>

      <h2 class="display display-lg reveal" style="margin-bottom:3rem;">Open positions</h2>

      <?php if (!$jobs): ?>
        <p>No open positions at the moment. We invite speculative applications at <a href="mailto:<?= e(setting('email_primary')) ?>" style="color:var(--color-accent);"><?= e(setting('email_primary')) ?></a>.</p>
      <?php endif; ?>

      <?php foreach ($jobs as $j): ?>
      <div class="reveal" style="border:1px solid var(--color-border); margin-bottom:1rem; background:var(--color-surface);">
        <button @click="open === <?= (int)$j['id'] ?> ? open = null : open = <?= (int)$j['id'] ?>" style="width:100%; padding:1.5rem 1.75rem; display:flex; justify-content:space-between; align-items:center; gap:1rem; text-align:left;">
          <div>
            <div class="eyebrow" style="margin-bottom:.5rem;"><?= e($j['department']) ?> · <?= e($j['employment_type']) ?> · <?= e($j['location']) ?></div>
            <h3 style="font-family:var(--font-display); font-weight:400; font-size:1.5rem; color:var(--color-text-primary);"><?= e($j['title']) ?></h3>
          </div>
          <span style="color:var(--color-accent); font-size:1.5rem;" x-text="open === <?= (int)$j['id'] ?> ? '−' : '+'">+</span>
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
              <button class="btn btn-primary" type="submit">Submit Application</button>
            </form>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>
<?php partial('footer'); ?>
