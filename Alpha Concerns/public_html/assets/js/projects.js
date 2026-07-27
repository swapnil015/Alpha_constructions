/* ===========================================================================
   Alpha Concern — Current Projects, cinematic panels
   ---------------------------------------------------------------------------
   Two sticky panels. The second rises over the first, which dims and settles
   back, so the pair reads as one continuous move rather than two cards.

   Video policy: nothing is fetched until a panel nears the viewport, playback
   runs only while the panel is on screen, and everything degrades to the
   poster frame if autoplay is refused (some browsers and every Low Power Mode
   iPhone will refuse).
   =========================================================================== */

(function () {
  'use strict';

  var section = document.querySelector('[data-cp]');
  if (!section) return;

  var panels = Array.prototype.slice.call(section.querySelectorAll('[data-cp-panel]'));
  var titleEl = section.querySelector('[data-cp-title]');
  if (!panels.length) return;

  var gsap = window.gsap;
  var ScrollTrigger = window.ScrollTrigger;
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // -------------------------------------------------------------------------
  // Video: lazy source, play only while visible
  // -------------------------------------------------------------------------
  var videos = Array.prototype.slice.call(section.querySelectorAll('[data-cp-video]'));

  function attach(video) {
    if (video.dataset.loaded) return;
    var src = video.dataset.src;
    if (!src) return;
    video.dataset.loaded = '1';
    video.src = src;
    video.load();
  }

  function play(video) {
    attach(video);
    var p = video.play();
    // Autoplay can be refused (Low Power Mode, data saver). The poster stays
    // visible, so there is nothing to repair — just don't throw.
    if (p && typeof p.catch === 'function') {
      p.catch(function () { video.closest('[data-cp-panel]').classList.add('is-noautoplay'); });
    }
  }

  if ('IntersectionObserver' in window) {
    // Pre-fetch a little before the panel arrives so playback starts on time.
    var preload = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) { attach(e.target); preload.unobserve(e.target); } });
    }, { rootMargin: '400px 0px' });

    var playback = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) play(e.target);
        else if (!e.target.paused) e.target.pause();
      });
    }, { threshold: 0.25 });

    videos.forEach(function (v) { preload.observe(v); playback.observe(v); });
  } else {
    videos.forEach(function (v) { play(v); });
  }

  // Never let a background film keep running in a hidden tab.
  document.addEventListener('visibilitychange', function () {
    videos.forEach(function (v) {
      if (document.hidden) { if (!v.paused) v.pause(); }
      else if (v.dataset.loaded && isOnScreen(v)) play(v);
    });
  });

  function isOnScreen(el) {
    var r = el.getBoundingClientRect();
    return r.bottom > 0 && r.top < window.innerHeight;
  }

  // -------------------------------------------------------------------------
  // Without GSAP the CSS fallback already shows everything in place.
  // -------------------------------------------------------------------------
  if (!gsap || !ScrollTrigger || reduceMotion) { section.classList.add('is-static'); return; }
  gsap.registerPlugin(ScrollTrigger);
  section.classList.add('is-live');

  // -------------------------------------------------------------------------
  // Heading — character by character
  // -------------------------------------------------------------------------
  if (titleEl) {
    var text = titleEl.textContent.trim();
    titleEl.textContent = '';
    text.split('').forEach(function (ch) {
      var span = document.createElement('span');
      span.className = 'cp__char';
      // A collapsed space would run the words together; keep it as a hard space.
      span.innerHTML = ch === ' ' ? '&nbsp;' : ch;
      titleEl.appendChild(span);
    });

    gsap.from(titleEl.querySelectorAll('.cp__char'), {
      yPercent: 110, opacity: 0, duration: 0.9, ease: 'power4.out', stagger: 0.028,
      scrollTrigger: { trigger: titleEl, start: 'top 88%', once: true }
    });
  }

  gsap.from(section.querySelectorAll('.cp__eyebrow, .cp__lede'), {
    y: 24, opacity: 0, duration: 0.9, ease: 'power4.out', stagger: 0.12,
    scrollTrigger: { trigger: section.querySelector('.cp__head'), start: 'top 86%', once: true }
  });

  // -------------------------------------------------------------------------
  // Panels
  // -------------------------------------------------------------------------
  panels.forEach(function (panel, i) {
    var media = panel.querySelector('.cp__media');
    var inner = panel.querySelector('.cp__zoom > *');
    var cat   = panel.querySelector('[data-cp-cat]');
    var name  = panel.querySelector('[data-cp-name]');
    var loc   = panel.querySelector('[data-cp-loc]');
    var cta   = panel.querySelector('.cp__cta');
    var badge = panel.querySelector('.cp__badge');

    // -- Reveal: wipe up, radius relaxes from 60px to 28px ------------------
    gsap.set(media, { clipPath: 'inset(100% 0% 0% 0%)', borderRadius: '60px' });

    var reveal = gsap.timeline({
      defaults: { ease: 'power4.out' },
      scrollTrigger: { trigger: panel, start: 'top 82%', once: true }
    });

    reveal.to(media, { clipPath: 'inset(0% 0% 0% 0%)', borderRadius: '28px', duration: 1.2 }, 0);

    // Very slow settle from 1.12 — deliberately close to unnoticeable.
    if (inner) reveal.fromTo(inner, { scale: 1.12 }, { scale: 1, duration: 16, ease: 'none' }, 0);

    reveal.from([badge, cat], { y: 18, opacity: 0, duration: 0.7, stagger: 0.08 }, 0.35);
    reveal.from(name,         { y: 20, opacity: 0, duration: 0.8 }, 0.45);
    reveal.from(loc,          { y: 14, opacity: 0, duration: 0.7 }, 0.62);
    reveal.from(cta,          { x: -12, opacity: 0, duration: 0.7 }, 0.7);

    // -- Hand-off: this panel recedes as the next one climbs over it -------
    var next = panels[i + 1];
    if (!next) return;

    gsap.timeline({
      scrollTrigger: {
        trigger: next,
        start: 'top bottom',
        end: 'top top',
        scrub: true          // tied to scroll, so both are visible together
      }
    })
      // fromTo, not to: the element's computed filter is 'none', which GSAP
      // interpolates as brightness(0) — the outgoing film snapped to black at
      // the start of the hand-off instead of dimming from full brightness.
      .fromTo(media,
        { scale: 1,    filter: 'brightness(1)' },
        { scale: 0.96, filter: 'brightness(0.6)', ease: 'none' }, 0)
      .fromTo(panel.querySelector('[data-cp-divider]'),
        { scaleX: 0 }, { scaleX: 1, ease: 'none' }, 0);

    // The incoming panel grows the last 4% as it takes over.
    gsap.fromTo(next.querySelector('.cp__media'),
      { scale: 0.96 },
      {
        scale: 1, ease: 'none',
        scrollTrigger: { trigger: next, start: 'top bottom', end: 'top center', scrub: true }
      });
  });

  document.addEventListener('alpha:layout', function () { ScrollTrigger.refresh(); });
  window.addEventListener('load', function () { ScrollTrigger.refresh(); });
})();
