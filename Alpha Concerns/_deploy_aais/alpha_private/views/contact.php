<?php
partial('head', compact('page_title'));
partial('header');
$flashes = flash_get();
$prefill = $_GET['subject'] ?? '';
?>
<main id="main">
  <section class="hero" style="min-height: 50vh;">
    <div class="hero__bg"></div>
    <div class="container hero__inner">
      <div class="eyebrow">Contact</div>
      <h1 class="hero__title" data-split-words>Let's talk</h1>
      <p class="hero__sub reveal">Send us a brief about your project, and we'll respond within one working day.</p>
    </div>
  </section>

  <section class="section section--cream">
    <div class="container split">
      <div>
        <?php foreach (($flashes['success'] ?? []) as $m): ?>
          <div class="form-success"><?= e($m) ?></div>
        <?php endforeach; ?>
        <?php foreach (($flashes['error'] ?? []) as $m): ?>
          <div class="form-success" style="border-color:#E8835F; color:#E8835F;"><?= e($m) ?></div>
        <?php endforeach; ?>

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
          <button class="btn btn-primary" type="submit">Send Message</button>
        </form>
      </div>

      <div>
        <div class="eyebrow reveal">Reach Us</div>
        <h2 class="display display-md reveal" style="margin: 1rem 0 2rem;">Kathmandu HQ</h2>

        <div style="margin-bottom:2rem;">
          <div class="eyebrow" style="margin-bottom:.75rem;">Address</div>
          <p><?= e(setting('address')) ?></p>
        </div>
        <div style="margin-bottom:2rem;">
          <div class="eyebrow" style="margin-bottom:.75rem;">Phone</div>
          <p><a href="tel:<?= e(setting('phone_tel', setting('phone_primary'))) ?>" style="color:var(--color-text-primary);"><?= e(setting('phone_primary')) ?></a></p>
        </div>
        <div style="margin-bottom:2rem;">
          <div class="eyebrow" style="margin-bottom:.75rem;">WhatsApp</div>
          <p><a href="https://wa.me/<?= e(setting('whatsapp_number')) ?>" target="_blank" rel="noopener" style="color:var(--color-text-primary);"><?= e(setting('whatsapp_display', setting('whatsapp_number'))) ?></a></p>
        </div>
        <div style="margin-bottom:2rem;">
          <div class="eyebrow" style="margin-bottom:.75rem;">Email</div>
          <p><a href="mailto:<?= e(setting('email_primary')) ?>" style="color:var(--color-text-primary);"><?= e(setting('email_primary')) ?></a></p>
        </div>
        <div style="margin-bottom:2rem;">
          <div class="eyebrow" style="margin-bottom:.75rem;">Hours</div>
          <p><?= e(setting('office_hours')) ?></p>
        </div>

        <div style="display:flex; gap:.75rem; flex-wrap:wrap;">
          <a href="https://wa.me/<?= e(setting('whatsapp_number')) ?>?text=<?= rawurlencode("Hello Alpha Concern, I'd like to learn more about your services.") ?>" class="btn btn-primary" target="_blank" rel="noopener">Chat on WhatsApp</a>
          <?php if ($shareUrl = setting('map_share_url')): ?>
          <a href="<?= e($shareUrl) ?>" class="btn btn-ghost" target="_blank" rel="noopener">Get Directions</a>
          <?php endif; ?>
        </div>

        <div style="margin-top:3rem; aspect-ratio:16/10; background:var(--color-surface); overflow:hidden;">
          <iframe src="<?= e(setting('map_embed_url')) ?>" width="100%" height="100%" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Alpha Concern office — Maharajgunj 4, Kathmandu"></iframe>
        </div>
      </div>
    </div>
  </section>
</main>
<?php partial('footer'); ?>
