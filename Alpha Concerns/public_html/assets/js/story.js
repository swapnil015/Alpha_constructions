/* ===========================================================================
   Alpha Concern — Pinned Scroll Storytelling
   ---------------------------------------------------------------------------
   A pinned editorial section whose whole timeline is scrubbed by scroll
   position. GSAP + ScrollTrigger; Lenis (initialised in main.js) supplies the
   smoothing and is already wired to ScrollTrigger.update there.

   Structure — the two-element card is deliberate:
     [data-story-card]   outer  → parallax drift only (one transform channel)
     [data-story-inner]  inner  → entrance (y / scale / rotation / opacity)
   Animating both on one element would make the entrance and the parallax fight
   over the same transform matrix. Splitting them keeps each tween independent
   and lets GSAP write a single composited translate3d per element.
   =========================================================================== */

(function () {
  'use strict';

  var section = document.querySelector('[data-story]');
  if (!section) return;

  var gsap = window.gsap;
  var ScrollTrigger = window.ScrollTrigger;

  // No GSAP (CDN blocked/offline) → leave the static, fully-visible fallback.
  if (!gsap || !ScrollTrigger) { section.classList.add('is-static'); return; }

  // Reduced motion: show the finished composition, skip the choreography.
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    section.classList.add('is-static');
    return;
  }

  gsap.registerPlugin(ScrollTrigger);

  var stage    = section.querySelector('[data-story-stage]');
  var headline = section.querySelector('[data-story-headline]');
  var cards    = gsap.utils.toArray('[data-story-card]', section);
  var blocks   = gsap.utils.toArray('[data-story-block]', section);
  if (!stage || !cards.length) return;

  section.classList.add('is-live');   // hands opacity control over to GSAP

  // Timeline units. Cards start every STAGGER and take ENTER to arrive, so a
  // card begins while the previous is ~30% from finishing — the overlap that
  // makes the sequence read as continuous motion rather than a slideshow.
  var ENTER   = 1.0;
  var STAGGER = 0.7;
  var TOTAL   = (cards.length - 1) * STAGGER + ENTER + 1.2;   // + tail for drift

  gsap.matchMedia().add(
    { desktop: '(min-width: 721px)', mobile: '(max-width: 720px)' },
    function (ctx) {
      var isMobile = ctx.conditions.mobile;

      // Pinned scroll distance. 350vh desktop / 260vh mobile.
      var travelVh = isMobile ? 2.6 : 3.5;

      var inners = cards.map(function (c) { return c.querySelector('[data-story-inner]'); });

      // Initial state, set imperatively so there is no flash of finished layout
      // before the first tick and no layout shift (transforms only).
      gsap.set(inners, { opacity: 0, y: 120, scale: 0.82, force3D: true });
      inners.forEach(function (el, i) {
        gsap.set(el, { rotation: i % 2 === 0 ? -3 : 3 });
      });
      gsap.set(cards, { y: 0, force3D: true });
      if (blocks.length) gsap.set(blocks, { opacity: 0, y: 40, force3D: true });

      var tl = gsap.timeline({
        defaults: { ease: 'power3.out', force3D: true },
        scrollTrigger: {
          trigger: section,
          start: 'top top',
          end: function () { return '+=' + Math.round(window.innerHeight * travelVh); },
          pin: stage,
          pinSpacing: true,
          anticipatePin: 1,
          // 1.6 = the playhead lags scroll by ~1.6s of catch-up. This is what
          // removes every abrupt edge; below ~1 the motion starts to feel
          // mechanical, above ~2 it feels detached from the wheel.
          scrub: isMobile ? 1.2 : 1.6,
          invalidateOnRefresh: true
        }
      });

      // ---- Cards ---------------------------------------------------------
      cards.forEach(function (card, i) {
        var at    = i * STAGGER;
        var inner = inners[i];
        var depth = parseFloat(card.dataset.depth) || 1;

        // Entrance
        tl.to(inner, {
          opacity: 1, y: 0, scale: 1, rotation: 0,
          duration: ENTER, ease: 'power3.out'
        }, at);

        // Parallax drift — runs from the moment the card appears to the end of
        // the timeline. Depth multiplies the distance, so nearer cards travel
        // further and the field separates into planes.
        tl.to(card, {
          y: -140 * depth,
          duration: Math.max(0.1, TOTAL - at),
          ease: 'none'
        }, at);
      });

      // ---- Headline ------------------------------------------------------
      // Slow, linear lift across the whole timeline — always readable, never
      // racing the cards.
      if (headline) {
        tl.to(headline, {
          y: isMobile ? -40 : -90,
          duration: TOTAL,
          ease: 'none'
        }, 0);
      }

      // ---- Copy blocks ---------------------------------------------------
      // Each block owns a slice of the timeline and cross-fades with the next.
      if (blocks.length) {
        var slice = TOTAL / blocks.length;
        blocks.forEach(function (block, j) {
          var at = j * slice;
          tl.to(block, {
            opacity: 1, y: 0, duration: 0.8, ease: 'power3.out'
          }, at);
          if (j < blocks.length - 1) {
            tl.to(block, {
              opacity: 0, y: -30, duration: 0.6, ease: 'power2.inOut'
            }, at + slice - 0.3);
          }
        });
      }

      // matchMedia reverts every tween and ScrollTrigger created in here when
      // the query stops matching.
      return function () { tl.kill(); };
    }
  );

  // -------------------------------------------------------------------------
  // Re-measure
  // -------------------------------------------------------------------------
  // The hero sequence sets its own height after its frames resolve, which moves
  // everything below it. Without this the pin would start at the wrong scroll
  // position on first load.
  document.addEventListener('alpha:layout', function () {
    ScrollTrigger.refresh();
  });

  // Card images are fixed-ratio boxes so they cause no reflow, but fonts and
  // any late-loading asset still can.
  window.addEventListener('load', function () { ScrollTrigger.refresh(); });
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(function () { ScrollTrigger.refresh(); });
  }
})();
