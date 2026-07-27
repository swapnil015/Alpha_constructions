<div class="preloader" aria-hidden="true">
  <img class="preloader__logo" src="<?= asset('assets/img/logo.jpg') ?>" alt="Alpha Concern Pvt. Ltd.">
  <div class="preloader__bar" aria-hidden="true"></div>
  <div class="preloader__sub">Building Tomorrow, Today</div>
</div>

<div class="topbar">
  <div class="container topbar__row">
    <div class="topbar__left">
      <span>EST. 2014 · KATHMANDU</span>
    </div>
    <div class="topbar__right">
      <a href="tel:<?= e(setting('phone_tel', setting('phone_primary'))) ?>"><?= e(setting('phone_primary')) ?></a>
      <span class="topbar__sep">|</span>
      <a href="mailto:<?= e(setting('email_primary')) ?>"><?= e(setting('email_primary')) ?></a>
      <span class="topbar__sep">|</span>
      <span><?= e(setting('office_hours')) ?></span>
    </div>
  </div>
</div>

<header class="site-header" role="banner">
  <div class="container nav-row">
    <a href="/" class="brand-mark" aria-label="Alpha Concern home">
      <img src="<?= asset('assets/img/logo.jpg') ?>" alt="Alpha Concern Pvt. Ltd.">
    </a>

    <nav class="nav-list" aria-label="Primary">
      <a href="/" class="nav-link <?= is_active('/') ? 'is-active' : '' ?>">Home</a>
      <a href="/about" class="nav-link <?= is_active('/about') ? 'is-active' : '' ?>">About</a>
      <a href="/services" class="nav-link <?= is_active('/services') ? 'is-active' : '' ?>">Services</a>
      <a href="/projects" class="nav-link <?= is_active('/projects') ? 'is-active' : '' ?>">Projects</a>
      <a href="/why-us" class="nav-link <?= is_active('/why-us') ? 'is-active' : '' ?>">Why Us</a>
      <a href="/blog" class="nav-link <?= is_active('/blog') ? 'is-active' : '' ?>">Insights</a>
      <a href="/careers" class="nav-link <?= is_active('/careers') ? 'is-active' : '' ?>">Careers</a>
      <a href="/contact" class="nav-link <?= is_active('/contact') ? 'is-active' : '' ?>">Contact</a>
    </nav>

    <a href="/contact" class="btn btn-primary nav-cta">Get a Quote</a>

    <button class="hamburger" aria-label="Toggle menu" aria-expanded="false" aria-controls="mobile-menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<div class="mobile-menu" id="mobile-menu" role="dialog" aria-label="Mobile menu">
  <a href="/">Home</a>
  <a href="/about">About</a>
  <a href="/services">Services</a>
  <a href="/projects">Projects</a>
  <a href="/why-us">Why Us</a>
  <a href="/blog">Insights</a>
  <a href="/careers">Careers</a>
  <a href="/contact">Contact</a>
</div>
