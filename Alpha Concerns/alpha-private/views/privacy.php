<?php partial('head', compact('page_title')); partial('header'); ?>
<main id="main">
  <section class="hero" style="min-height:40vh;">
    <div class="hero__bg"></div>
    <div class="container hero__inner">
      <div class="eyebrow">Legal</div>
      <h1 class="hero__title">Privacy Policy</h1>
    </div>
  </section>
  <section class="section section--cream">
    <div class="container-md" style="color:var(--color-text-secondary);">
      <p>Effective <?= date('F j, Y') ?>. This policy describes how <?= e(SITE_NAME) ?> collects and uses information you provide via this website.</p>
      <h2 class="heading heading-lg" style="margin:2rem 0 1rem;">Information we collect</h2>
      <p>When you submit a contact, project enquiry, or career application form, we collect the information you provide (such as your name, email, phone number, message, and — for career applications — your CV).</p>
      <h2 class="heading heading-lg" style="margin:2rem 0 1rem;">How we use it</h2>
      <p>We use this information solely to respond to your enquiry and, where applicable, to evaluate your application. We do not sell or share your data with third parties.</p>
      <h2 class="heading heading-lg" style="margin:2rem 0 1rem;">Cookies</h2>
      <p>We use cookies for analytics (Google Analytics 4) only when you accept the cookie banner. You can decline at any time.</p>
      <h2 class="heading heading-lg" style="margin:2rem 0 1rem;">Contact</h2>
      <p>For any privacy questions, email <a href="mailto:<?= e(setting('email_primary')) ?>" style="color:var(--color-accent);"><?= e(setting('email_primary')) ?></a>.</p>
    </div>
  </section>
</main>
<?php partial('footer'); ?>
