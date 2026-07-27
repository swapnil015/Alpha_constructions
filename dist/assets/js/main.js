/* Alpha Concern — front-end JS
 * Depends on GSAP, ScrollTrigger, Lenis (loaded via CDN in <head>)
 */
(function () {
  'use strict';

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ---------------------------------------------------------------------
  // Preloader (always shown for at least 1.4s; force-skipped only if reduced motion)
  // ---------------------------------------------------------------------
  const preloader = document.querySelector('.preloader');
  if (preloader) {
    if (reduceMotion) {
      preloader.style.display = 'none';
    } else {
      const finish = () => preloader.classList.add('is-done');
      const minShow = 1900;
      const start = performance.now();
      const ready = () => {
        const elapsed = performance.now() - start;
        const wait = Math.max(0, minShow - elapsed);
        setTimeout(finish, wait);
      };
      if (document.readyState === 'complete') ready();
      else window.addEventListener('load', ready);
      setTimeout(finish, 3500); // safety
    }
  }

  // ---------------------------------------------------------------------
  // Smooth scroll (Lenis) — slow, premium ease. Desktop only.
  // ---------------------------------------------------------------------
  if (window.Lenis && !reduceMotion && window.matchMedia('(pointer: fine)').matches) {
    const lenis = new window.Lenis({
      duration:        1.8,                                  // slower, more deliberate (default 1.0)
      easing:          (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)), // expo-out — long graceful tail
      smoothWheel:     true,
      wheelMultiplier: 0.85,                                 // softer wheel sensitivity
      lerp:            0.085,                                // lower = silkier
      orientation:     'vertical',
      gestureOrientation: 'vertical',
    });
    function raf(t) { lenis.raf(t); requestAnimationFrame(raf); }
    requestAnimationFrame(raf);
    if (window.gsap && window.ScrollTrigger) {
      lenis.on('scroll', window.ScrollTrigger.update);
      window.gsap.ticker.add((time) => lenis.raf(time * 1000));
      window.gsap.ticker.lagSmoothing(0);
    }
    // Expose for any nav anchor smooth-scroll
    window.__lenis = lenis;

    // Anchor links (e.g. hero "scroll" indicator) → use Lenis
    document.querySelectorAll('a[href^="#"]').forEach(a => {
      a.addEventListener('click', (e) => {
        const id = a.getAttribute('href');
        if (id.length > 1) {
          const tgt = document.querySelector(id);
          if (tgt) { e.preventDefault(); lenis.scrollTo(tgt, { offset: -80, duration: 2.0 }); }
        }
      });
    });
  }

  // ---------------------------------------------------------------------
  // Header scroll state
  // ---------------------------------------------------------------------
  const header = document.querySelector('.site-header');
  if (header) {
    const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 60);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // ---------------------------------------------------------------------
  // Mobile menu
  // ---------------------------------------------------------------------
  const ham = document.querySelector('.hamburger');
  const mob = document.querySelector('.mobile-menu');
  if (ham && mob) {
    ham.addEventListener('click', () => {
      const open = mob.classList.toggle('is-open');
      ham.classList.toggle('is-open', open);
      ham.setAttribute('aria-expanded', open ? 'true' : 'false');
      document.body.style.overflow = open ? 'hidden' : '';
    });
    mob.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
      mob.classList.remove('is-open'); ham.classList.remove('is-open');
      document.body.style.overflow = '';
    }));
  }

  // ---------------------------------------------------------------------
  // Cookie bar
  // ---------------------------------------------------------------------
  const cookie = document.querySelector('.cookie-bar');
  if (cookie) {
    if (!localStorage.getItem('alpha_cookie_choice')) {
      setTimeout(() => cookie.classList.add('is-visible'), 2200);
    }
    cookie.querySelector('[data-cookie="accept"]')?.addEventListener('click', () => {
      localStorage.setItem('alpha_cookie_choice', 'accept');
      cookie.classList.remove('is-visible');
    });
    cookie.querySelector('[data-cookie="decline"]')?.addEventListener('click', () => {
      localStorage.setItem('alpha_cookie_choice', 'decline');
      const id = document.documentElement.dataset.gaId;
      if (id) window['ga-disable-' + id] = true;
      cookie.classList.remove('is-visible');
    });
  }

  // ---------------------------------------------------------------------
  // Back to top
  // ---------------------------------------------------------------------
  const top = document.querySelector('.floater-top');
  if (top) {
    // Visibility is owned by motion.js (600px threshold). Two listeners
    // toggling the same class at different thresholds fight between them.
    top.addEventListener('click', () => {
      if (window.__lenis) window.__lenis.scrollTo(0, { duration: 2.4 });
      else window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // ---------------------------------------------------------------------
  // Custom cursor — small filled dot + larger trailing ring
  // ---------------------------------------------------------------------
  if (window.matchMedia('(pointer: fine)').matches && window.matchMedia('(hover: hover)').matches && !reduceMotion) {
    const dot  = document.createElement('div'); dot.className  = 'cursor-dot';
    const ring = document.createElement('div'); ring.className = 'cursor-ring';
    document.body.appendChild(dot); document.body.appendChild(ring);

    let mx = window.innerWidth / 2, my = window.innerHeight / 2;
    let rx = mx, ry = my; // ring lerps toward mouse

    document.addEventListener('mousemove', (e) => { mx = e.clientX; my = e.clientY; }, { passive: true });
    document.addEventListener('mouseleave', () => { dot.style.opacity = '0'; ring.style.opacity = '0'; });
    document.addEventListener('mouseenter', () => { dot.style.opacity = '1'; ring.style.opacity = '1'; });

    function loop() {
      // Dot — exact position (snappy)
      dot.style.transform  = `translate(${mx}px, ${my}px) translate(-50%, -50%)`;
      // Ring — lerped (soft trailing)
      rx += (mx - rx) * 0.16;
      ry += (my - ry) * 0.16;
      ring.style.transform = `translate(${rx}px, ${ry}px) translate(-50%, -50%)`;
      requestAnimationFrame(loop);
    } loop();

    document.addEventListener('mouseover', (e) => {
      if (e.target.closest('a, button, .project-card, .service-card, [data-cursor]')) ring.classList.add('is-hover');
    });
    document.addEventListener('mouseout', (e) => {
      if (e.target.closest('a, button, .project-card, .service-card, [data-cursor]')) ring.classList.remove('is-hover');
    });
  }

  // ---------------------------------------------------------------------
  // GSAP scroll reveals
  // ---------------------------------------------------------------------
  if (window.gsap && window.ScrollTrigger && !reduceMotion) {
    window.gsap.registerPlugin(window.ScrollTrigger);

    document.querySelectorAll('.reveal').forEach((el) => {
      window.gsap.to(el, {
        opacity: 1, y: 0, duration: 1.0, ease: 'power3.out',
        scrollTrigger: { trigger: el, start: 'top 88%', once: true },
        onComplete: () => el.classList.add('is-revealed')
      });
    });

    // Hero word reveal
    document.querySelectorAll('[data-split-words]').forEach((el) => {
      const text = el.textContent.trim();
      el.innerHTML = text.split(' ').map(w =>
        `<span class="split-line"><span>${w}</span></span>`
      ).join(' ');
      const inners = el.querySelectorAll('.split-line > span');
      window.gsap.to(inners, {
        y: 0, duration: 1.2, stagger: .1, ease: 'power4.out', delay: .35
      });
    });

    // Stat counters
    document.querySelectorAll('[data-count]').forEach((el) => {
      const target = parseFloat(el.dataset.count) || 0;
      const obj = { v: 0 };
      window.gsap.to(obj, {
        v: target, duration: 2, ease: 'power2.out',
        scrollTrigger: { trigger: el, start: 'top 88%', once: true },
        onUpdate: () => { el.textContent = Math.floor(obj.v); }
      });
    });
  }

  // ---------------------------------------------------------------------
  // Project filter (instant client-side)
  // ---------------------------------------------------------------------
  document.querySelectorAll('[data-filter-group]').forEach((group) => {
    const tabs = group.querySelectorAll('.filter-tab');
    const cards = group.querySelectorAll('[data-cat]');
    tabs.forEach(tab => tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('is-active'));
      tab.classList.add('is-active');
      const cat = tab.dataset.cat;
      cards.forEach(c => {
        const show = cat === 'all' || c.dataset.cat === cat;
        c.style.display = show ? '' : 'none';
      });
    }));
  });

  // ---------------------------------------------------------------------
  // Testimonial carousel
  // ---------------------------------------------------------------------
  const tCarousel = document.querySelector('[data-testimonials]');
  if (tCarousel) {
    const slides = tCarousel.querySelectorAll('.testimonial');
    let idx = 0;
    const show = (i) => slides.forEach((s, k) => s.style.display = k === i ? '' : 'none');
    show(0);
    tCarousel.querySelector('[data-prev]')?.addEventListener('click', () => { idx = (idx - 1 + slides.length) % slides.length; show(idx); });
    tCarousel.querySelector('[data-next]')?.addEventListener('click', () => { idx = (idx + 1) % slides.length; show(idx); });
  }
})();
