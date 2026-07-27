/* ===========================================================================
   Alpha Concern — Why Us page motion
   ---------------------------------------------------------------------------
   The generic reveal engine lives in motion.js. This file only handles what is
   specific to this page: the hero line reveal and stat counters, the sticky
   index scroll-spy and its progress rail, the self-drawing icons, and the
   one-shot CTA pulse.
   =========================================================================== */

(function () {
  'use strict';

  var page = document.querySelector('[data-why-us]');
  if (!page) return;

  var M = window.Motion;
  var gsap = window.gsap;
  var ST = window.ScrollTrigger;
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var q  = function (s) { return page.querySelector(s); };
  var qa = function (s) { return Array.prototype.slice.call(page.querySelectorAll(s)); };

  // -------------------------------------------------------------------------
  // Icons: draw themselves from a real measured path length
  // -------------------------------------------------------------------------
  qa('[data-wy-icon]').forEach(function (svg) {
    var shapes = Array.prototype.slice.call(svg.querySelectorAll('path, rect, circle, line, polyline'));
    shapes.forEach(function (s) {
      // getTotalLength is unavailable on <rect> in some engines; fall back to
      // the bounding perimeter so the dash still resolves.
      var len;
      try { len = s.getTotalLength(); } catch (e) { len = 0; }
      if (!len) {
        var b = s.getBBox();
        len = (b.width + b.height) * 2;
      }
      s.style.strokeDasharray  = len;
      s.style.strokeDashoffset = reduced ? 0 : len;
      s.style.transition = 'stroke-dashoffset 1.2s var(--ease-out)';
    });

    if (reduced || !('IntersectionObserver' in window)) return;

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        shapes.forEach(function (s, i) {
          s.style.transitionDelay = (i * 0.06) + 's';
          s.style.strokeDashoffset = 0;
        });
        io.unobserve(e.target);
      });
    }, { threshold: 0.3 });
    io.observe(svg);

    // Same failsafe as the reveal engine: never leave an icon half-drawn.
    setTimeout(function () {
      shapes.forEach(function (s) { s.style.strokeDashoffset = 0; });
    }, 4500);
  });

  if (!gsap || !ST || reduced) {
    // Without GSAP the CSS resting states already show everything; just make
    // sure the hero lines are not left sitting below their mask.
    qa('[data-wy-title] .split-line > span').forEach(function (el) { el.style.transform = 'none'; });
    qa('[data-wy-stats] .wy-stat').forEach(function (el) { el.style.opacity = 1; });
    qa('[data-wy-count]').forEach(function (el) { el.textContent = el.dataset.wyCount; });
    return;
  }

  gsap.registerPlugin(ST);

  // -------------------------------------------------------------------------
  // Hero
  // -------------------------------------------------------------------------
  var lines = qa('[data-wy-title] .split-line > span');
  if (lines.length) {
    gsap.set(lines, { yPercent: 110 });
    gsap.to(lines, {
      yPercent: 0, duration: 1, ease: 'power4.out',
      stagger: 0.12, delay: 0.25
    });
  }

  var sub = q('[data-wy-sub]');
  if (sub) {
    gsap.set(sub, { opacity: 0, y: 24 });
    // Lands after both headline lines have arrived.
    gsap.to(sub, { opacity: 1, y: 0, duration: 0.9, ease: 'power4.out', delay: 1.15 });
  }

  var stats = qa('[data-wy-stats] .wy-stat');
  if (stats.length) {
    gsap.set(stats, { opacity: 0, y: 22 });
    gsap.to(stats, {
      opacity: 1, y: 0, duration: 0.9, ease: 'power4.out',
      stagger: 0.15, delay: 0.9
    });
  }

  qa('[data-wy-count]').forEach(function (el, i) {
    var target = parseInt(el.dataset.wyCount, 10) || 0;
    var obj = { v: 0 };
    gsap.to(obj, {
      v: target, duration: 1.4, ease: 'power3.out', delay: 1 + i * 0.15,
      onUpdate: function () { el.textContent = Math.round(obj.v); },
      onComplete: function () { el.textContent = target; }
    });
  });

  // Slow parallax on the headline block as the hero passes.
  var heroBlock = q('[data-wy-heroblock]');
  if (heroBlock) {
    gsap.to(heroBlock, {
      y: -40, ease: 'none',
      scrollTrigger: {
        trigger: q('.wy-hero'),
        start: 'top top', end: 'bottom top',
        scrub: true, invalidateOnRefresh: true
      }
    });
  }

  // -------------------------------------------------------------------------
  // Sticky index: scroll-spy + progress rail
  // -------------------------------------------------------------------------
  var blocks = qa('[data-wy-block]');
  var items  = qa('[data-wy-idx]');

  function setActive(i) {
    items.forEach(function (el, k) { el.classList.toggle('is-active', k === i); });
  }

  blocks.forEach(function (block, i) {
    ST.create({
      trigger: block,
      // The block owns the index entry while it holds the middle of the screen.
      start: 'top 55%',
      end: 'bottom 55%',
      onEnter:     function () { setActive(i); },
      onEnterBack: function () { setActive(i); }
    });
  });

  var rail = q('[data-wy-rail]');
  var reasons = q('[data-wy-reasons]');
  if (rail && reasons) {
    gsap.fromTo(rail, { scaleY: 0 }, {
      scaleY: 1, ease: 'none', transformOrigin: 'top center',
      scrollTrigger: {
        trigger: reasons,
        start: 'top 60%', end: 'bottom 80%',
        scrub: 0.6, invalidateOnRefresh: true
      }
    });
  }

  // Index links scroll through Lenis when it is driving, so the two do not
  // fight over scroll position.
  items.forEach(function (item) {
    item.addEventListener('click', function (e) {
      var target = document.querySelector(item.getAttribute('href'));
      if (!target) return;
      e.preventDefault();
      if (window.__lenis) window.__lenis.scrollTo(target, { offset: -140, duration: 1.2 });
      else target.scrollIntoView({ behavior: 'smooth' });
    });
  });

  // -------------------------------------------------------------------------
  // Testimonials: cursor-following tint
  // -------------------------------------------------------------------------
  if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    qa('.wy-quote').forEach(function (card) {
      card.addEventListener('mousemove', function (e) {
        var r = card.getBoundingClientRect();
        card.style.setProperty('--mx', ((e.clientX - r.left) / r.width * 100) + '%');
        card.style.setProperty('--my', ((e.clientY - r.top) / r.height * 100) + '%');
      }, { passive: true });
    });
  }

  // -------------------------------------------------------------------------
  // CTA: a single glow pulse the first time it arrives
  // -------------------------------------------------------------------------
  var cta = q('[data-wy-cta]');
  if (cta) {
    ST.create({
      trigger: cta, start: 'top 85%', once: true,
      onEnter: function () {
        setTimeout(function () { cta.classList.add('is-pulse'); }, 400);
      }
    });
  }

  // -------------------------------------------------------------------------
  // Re-measure
  // -------------------------------------------------------------------------
  window.addEventListener('load', function () { ST.refresh(); });
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(function () { ST.refresh(); });
  }
  var t = null;
  window.addEventListener('resize', function () {
    clearTimeout(t);
    t = setTimeout(function () { ST.refresh(); }, 200);
  }, { passive: true });
})();
