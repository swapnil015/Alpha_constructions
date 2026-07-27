<footer class="site-footer" role="contentinfo">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="/" aria-label="Alpha Concern home">
          <img src="<?= asset('assets/img/logo.jpg') ?>" alt="Alpha Concern Pvt. Ltd.">
        </a>
        <p style="margin-top:1rem; max-width:32ch; font-size:.9rem;">
          <?= e(setting('description', 'Premium construction and real estate development in Kathmandu, Nepal.')) ?>
        </p>
      </div>

      <div>
        <div class="footer-heading">Navigate</div>
        <ul class="footer-list">
          <li><a href="/about">About Us</a></li>
          <li><a href="/services">Services</a></li>
          <li><a href="/projects">Projects</a></li>
          <li><a href="/blog">Insights</a></li>
          <li><a href="/careers">Careers</a></li>
        </ul>
      </div>

      <div>
        <div class="footer-heading">Services</div>
        <ul class="footer-list">
          <li><a href="/services/residential">Residential</a></li>
          <li><a href="/services/commercial">Commercial</a></li>
          <li><a href="/services/real-estate">Real Estate</a></li>
          <li><a href="/services/interior-design">Interiors</a></li>
          <li><a href="/services/structural-engineering">Engineering</a></li>
        </ul>
      </div>

      <div>
        <div class="footer-heading">Contact</div>
        <ul class="footer-list">
          <li><?= e(setting('address', 'Kathmandu, Nepal')) ?></li>
          <li><a href="tel:<?= e(setting('phone_tel', setting('phone_primary'))) ?>"><?= e(setting('phone_primary')) ?></a></li>
          <li><a href="https://wa.me/<?= e(setting('whatsapp_number')) ?>" target="_blank" rel="noopener">WhatsApp: <?= e(setting('whatsapp_display', setting('whatsapp_number'))) ?></a></li>
          <li><a href="mailto:<?= e(setting('email_primary')) ?>"><?= e(setting('email_primary')) ?></a></li>
          <li style="font-size:.8rem; color:var(--color-text-muted); margin-top:.85rem;"><?= e(setting('office_hours')) ?></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <div>© <?= date('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved. · <a href="/privacy">Privacy Policy</a></div>
      <div class="social-row">
        <?php if ($u = setting('social_facebook')): ?><a href="<?= e($u) ?>" aria-label="Facebook" target="_blank" rel="noopener">f</a><?php endif; ?>
        <?php if ($u = setting('social_instagram')): ?><a href="<?= e($u) ?>" aria-label="Instagram" target="_blank" rel="noopener">i</a><?php endif; ?>
        <?php if ($u = setting('social_linkedin')): ?><a href="<?= e($u) ?>" aria-label="LinkedIn" target="_blank" rel="noopener">in</a><?php endif; ?>
        <?php if ($u = setting('social_youtube')): ?><a href="<?= e($u) ?>" aria-label="YouTube" target="_blank" rel="noopener">▶</a><?php endif; ?>
      </div>
    </div>
  </div>
</footer>

<a class="floater-whatsapp" href="https://wa.me/<?= e(setting('whatsapp_number','977')) ?>?text=<?= rawurlencode("Hello, I'm interested in Alpha Concern's services.") ?>" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
  <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.52 3.48A11.83 11.83 0 0 0 12 0C5.37 0 0 5.37 0 12a11.93 11.93 0 0 0 1.64 6.04L0 24l6.16-1.62A11.97 11.97 0 0 0 12 24c6.63 0 12-5.37 12-12 0-3.19-1.25-6.19-3.48-8.52ZM12 21.82a9.78 9.78 0 0 1-4.99-1.36l-.36-.21-3.66.96.98-3.56-.23-.37A9.81 9.81 0 1 1 21.82 12c0 5.41-4.41 9.82-9.82 9.82Zm5.39-7.36c-.29-.15-1.74-.86-2.01-.96-.27-.1-.47-.15-.66.15-.2.29-.76.96-.93 1.16-.17.2-.34.22-.63.07-.29-.15-1.24-.46-2.36-1.45a8.86 8.86 0 0 1-1.64-2.04c-.17-.29-.02-.45.13-.6.13-.13.29-.34.44-.51.15-.17.2-.29.29-.49.1-.2.05-.37-.02-.51-.07-.15-.66-1.59-.91-2.18-.24-.58-.49-.5-.66-.5h-.56c-.2 0-.51.07-.78.37-.27.29-1.02 1-1.02 2.44s1.04 2.83 1.19 3.02c.15.2 2.05 3.13 4.97 4.39.69.3 1.23.48 1.65.61.69.22 1.32.19 1.82.12.55-.08 1.74-.71 1.99-1.4.24-.69.24-1.28.17-1.4-.07-.12-.27-.2-.56-.34Z"/></svg>
</a>

<button class="floater-top" aria-label="Back to top">↑</button>

<div class="cookie-bar" role="dialog" aria-label="Cookie consent">
  <div>We use cookies to improve your experience and analyse site traffic.</div>
  <div class="cookie-bar__btns">
    <button class="btn btn-decline" data-cookie="decline">Decline</button>
    <button class="btn btn-primary" data-cookie="accept">Accept</button>
  </div>
</div>

<!-- JS libs -->
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<?php /* NB: lenis@1.0.42 does not exist on npm — that version shipped under the
        @studio-freight scope, so the previous URL 404'd and smooth scrolling
        was silently dead. The unscoped `lenis` package only starts at 1.1.x. */ ?>
<script src="https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1.0.42/dist/lenis.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js" defer></script>
<script src="<?= asset('assets/js/main.js') ?>"></script>
<?php /* Shared motion primitives + site chrome (nav, CTA, header, scroll-top). */ ?>
<script src="<?= asset('assets/js/motion.js') ?>"></script>
<?php /* Per-page scripts — a view sets $page_scripts before including this. */ ?>
<?= $page_scripts ?? '' ?>
</body>
</html>
