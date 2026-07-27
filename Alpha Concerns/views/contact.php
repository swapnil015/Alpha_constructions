<?php
partial('head', compact('page_title'));
partial('header');
$flashes = flash_get();
$prefill = $_GET['subject'] ?? '';
?>
<main id="main">
  <section class="hero hero--shot pg-hero" style="min-height:56vh;">
    <img class="hero--shot__img" src="<?= asset('assets/img/hero/why-us-building.jpg') ?>"
         alt="A glazed commercial building at sunset above the Kathmandu valley."
         width="1672" height="941" fetchpriority="high" decoding="async">
    <span class="hero--shot__scrim" aria-hidden="true"></span>
    <div class="container hero__inner hero--shot__inner">
      <div class="eyebrow">Contact</div>
      <h1 class="hero__title" data-masked>Let's talk</h1>
      <p class="hero__sub reveal" data-reveal="up">Send us a brief about your project, and we'll respond within one working day.</p>
    </div>
  </section>

  <section class="section section--cream">
    <div class="container ct-grid">

      <div class="ct-panel reveal" data-reveal="up">
        <?php foreach (($flashes['success'] ?? []) as $m): ?>
          <div class="form-success"><?= e($m) ?></div>
        <?php endforeach; ?>
        <?php foreach (($flashes['error'] ?? []) as $m): ?>
          <div class="form-success" style="border-color:#E8835F; color:#E8835F;"><?= e($m) ?></div>
        <?php endforeach; ?>

        <div class="eyebrow" style="margin-bottom:1.5rem;">Project Brief</div>

        <form action="/api/contact.php" method="post" novalidate>
          <?= csrf_field() ?>
          <input type="text" name="website" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">

          <div class="form-group">
            <label class="form-label" for="name">Name *</label>
            <input class="form-control" type="text" id="name" name="name" required maxlength="150">
          </div>
          <div class="form-group">
            <label class="form-label" for="email">Email *</label>
            <input class="form-control" type="email" id="email" name="email" required maxlength="200">
          </div>
          <div class="form-group">
            <label class="form-label" for="phone">Phone *</label>
            <input class="form-control" type="tel" id="phone" name="phone" required maxlength="30">
          </div>
          <div class="form-group">
            <label class="form-label" for="subject">Subject</label>
            <select class="form-control" id="subject" name="subject">
              <?php foreach (['General Enquiry','Project Enquiry','Quote Request','Career'] as $opt): ?>
              <option <?= $opt === $prefill ? 'selected' : '' ?>><?= e($opt) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="message">Message *</label>
            <textarea class="form-control" id="message" name="message" required maxlength="3000"></textarea>
          </div>
          <button class="btn btn-primary is-magnetic" type="submit">Send Message</button>
        </form>
      </div>

      <div>
        <div class="eyebrow reveal" data-reveal="up">Reach Us</div>
        <h2 class="display display-md reveal" data-reveal="mask" style="margin: 1rem 0 2rem;"><span>Kathmandu HQ</span></h2>

        <div class="ct-lines reveal--stagger">
          <div class="ct-line reveal" data-reveal="up">
            <div class="eyebrow">Address</div>
            <p><?= e(setting('address')) ?></p>
          </div>
          <div class="ct-line reveal" data-reveal="up">
            <div class="eyebrow">Phone</div>
            <p><a href="tel:<?= e(setting('phone_tel', setting('phone_primary'))) ?>"><?= e(setting('phone_primary')) ?></a></p>
          </div>
          <div class="ct-line reveal" data-reveal="up">
            <div class="eyebrow">WhatsApp</div>
            <p><a href="https://wa.me/<?= e(setting('whatsapp_number')) ?>" target="_blank" rel="noopener"><?= e(setting('whatsapp_display', setting('whatsapp_number'))) ?></a></p>
          </div>
          <div class="ct-line reveal" data-reveal="up">
            <div class="eyebrow">Email</div>
            <p><a href="mailto:<?= e(setting('email_primary')) ?>"><?= e(setting('email_primary')) ?></a></p>
          </div>
          <div class="ct-line reveal" data-reveal="up" style="border-bottom:0;">
            <div class="eyebrow">Hours</div>
            <p class="ct-hours"><span class="ct-dot" aria-hidden="true"></span><?= e(setting('office_hours')) ?></p>
          </div>
        </div>

        <div style="display:flex; gap:.75rem; flex-wrap:wrap; margin-top:2rem;" class="reveal" data-reveal="up">
          <a href="https://wa.me/<?= e(setting('whatsapp_number')) ?>?text=<?= rawurlencode("Hello Alpha Concern, I'd like to learn more about your services.") ?>" class="btn btn-primary is-magnetic" target="_blank" rel="noopener">Chat on WhatsApp</a>
          <?php if ($shareUrl = setting('map_share_url')): ?>
          <a href="<?= e($shareUrl) ?>" class="btn btn-ghost is-magnetic" target="_blank" rel="noopener">Get Directions</a>
          <?php endif; ?>
        </div>

        <div class="reveal" data-reveal="wipe" style="margin-top:3rem; aspect-ratio:16/10; background:var(--color-surface); overflow:hidden; border-radius:16px;">
          <iframe src="<?= e(setting('map_embed_url')) ?>" width="100%" height="100%" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Alpha Concern office — Maharajgunj 4, Kathmandu"></iframe>
        </div>
      </div>

    </div>
  </section>
</main>
<?php partial('footer'); ?>
