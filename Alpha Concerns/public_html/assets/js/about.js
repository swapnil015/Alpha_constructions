/* ===========================================================================
   Alpha Concern — About page motion
   ---------------------------------------------------------------------------
   Applies the shared primitives in assets/js/motion.js section by section.
   No layout, colour, type or spacing is touched here — only motion.
   =========================================================================== */

(function () {
  'use strict';

  var M = window.Motion;
  if (!M) return;

  var page = document.querySelector('[data-about]');
  if (!page) return;

  var t = M.tokens;
  var q  = function (s, r) { return (r || page).querySelector(s); };
  var qa = function (s, r) { return Array.prototype.slice.call((r || page).querySelectorAll(s)); };

  // Labels everywhere on the page: divider draws, then the label fades in as
  // its tracking contracts.
  qa('.eyebrow').forEach(function (el) { M.label(el); });

  // -------------------------------------------------------------------------
  // 1. Hero — "Who We Are"
  // -------------------------------------------------------------------------
  var heroTitle = q('[data-about-hero-title]');
  if (heroTitle) {
    // The site-wide word splitter would fight the masked reveal.
    heroTitle.removeAttribute('data-split-words');
    M.maskedText(heroTitle, { stagger: t.stagger.base, duration: t.duration.slow });
  }

  var heroSub = q('[data-about-hero-sub]');
  // Delayed so it lands after the three headline lines have finished.
  if (heroSub) M.fadeUp(heroSub, { delay: 0.55, y: 24 });

  // Slow parallax: the headline block drifts up behind the scroll.
  var heroBlock = q('[data-about-hero]');
  if (heroBlock) M.parallax(heroBlock, -40, { trigger: heroBlock.closest('section') });

  // -------------------------------------------------------------------------
  // 2. Our Story
  // -------------------------------------------------------------------------
  var media = q('[data-about-media]');
  if (media && M.ready) {
    var gsap = window.gsap;
    gsap.set(media, { clipPath: 'inset(0% 100% 0% 0%)' });
    M.reveal(media, function (instant) {
      if (instant) { gsap.set(media, { clipPath: 'inset(0% 0% 0% 0%)' }); return; }
      gsap.to(media, {
        clipPath: 'inset(0% 0% 0% 0%)',
        duration: t.duration.line, ease: t.gsapEase,
        // The gold frame draws itself once the wipe is under way.
        onStart: function () { media.classList.add('is-framed'); }
      });
    });
    // Gentle continuous drift inside the panel.
    M.parallax(q('.split__media-inner', media) || media, 24, { trigger: media });
  }

  var storyTitle = q('[data-about-story-title]');
  if (storyTitle) M.maskedText(storyTitle, { stagger: t.stagger.cards });

  M.fadeUp(qa('[data-about-story-copy] p'), { stagger: t.stagger.cards, delay: 0.2 });

  // -------------------------------------------------------------------------
  // 3 & 6. Mission / Vision / Values, Leadership — card grids
  // -------------------------------------------------------------------------
  qa('[data-about-cards]').forEach(function (grid) {
    M.fadeUp(qa('.service-card', grid), { y: 40, stagger: t.stagger.cards, trigger: grid });
  });

  // -------------------------------------------------------------------------
  // 4. Stats
  // -------------------------------------------------------------------------
  var statsGrid = q('[data-about-stats]');
  if (statsGrid) {
    qa('.stat', statsGrid).forEach(function (stat) {
      M.countUp(q('[data-countup]', stat), { plus: q('.stat__plus', stat), trigger: statsGrid });
    });
    M.fadeUp(qa('.stat__label', statsGrid), { y: 14, stagger: t.stagger.tight, trigger: statsGrid, delay: 0.35 });

    if (M.ready) {
      var gsap2 = window.gsap;
      var divs = qa('.stat', statsGrid).slice(1);
      gsap2.set(divs, { '--rule': 0 });
      M.reveal(statsGrid, function (instant) {
        if (instant) { gsap2.set(divs, { '--rule': 1 }); return; }
        gsap2.to(divs, { '--rule': 1, duration: t.duration.fast, ease: t.gsapEase, stagger: t.stagger.tight });
      });
    }
  }

  // -------------------------------------------------------------------------
  // 5. Our Process
  // -------------------------------------------------------------------------
  var procTitle = q('[data-about-process-title]');
  if (procTitle) M.maskedText(procTitle, { stagger: t.stagger.cards });

  var procGrid = q('[data-about-process]');
  if (procGrid && M.ready) {
    var g = window.gsap;
    var cards = qa('.service-card', procGrid);

    // Diagonal cascade: 01, then 02+04, then 03+05, then 06. In a 3-column
    // grid that is (row + column), so the wave runs top-left to bottom-right.
    var cols = 3;
    g.set(cards, { opacity: 0, y: 40 });
    M.reveal(procGrid, function (instant) {
      if (instant) { g.set(cards, { opacity: 1, y: 0 }); return; }
      cards.forEach(function (card, i) {
        var diag = Math.floor(i / cols) + (i % cols);
        g.to(card, {
          opacity: 1, y: 0,
          duration: t.duration.base, ease: t.gsapEase,
          delay: diag * t.stagger.cards
        });
      });
    });

    // Numbers resolve from blurred to sharp.
    var nums = qa('.why-item__num', procGrid);
    g.set(nums, { opacity: 0, filter: 'blur(8px)' });
    M.reveal(procGrid, function (instant) {
      if (instant) { g.set(nums, { opacity: 1, filter: 'blur(0px)' }); return; }
      nums.forEach(function (n, i) {
        var diag = Math.floor(i / cols) + (i % cols);
        g.to(n, {
          opacity: 1, filter: 'blur(0px)',
          duration: 0.9, ease: t.gsapEase,
          delay: diag * t.stagger.cards + 0.1
        });
      });
    });

    // Vertical progress rule down the left of the section.
    var rail = q('[data-about-rail]');
    if (rail) {
      g.fromTo(rail, { scaleY: 0 }, {
        scaleY: 1, ease: 'none', transformOrigin: 'top center',
        scrollTrigger: {
          trigger: procGrid.closest('section'),
          start: 'top 70%', end: 'bottom 90%',
          scrub: 0.6, invalidateOnRefresh: true
        }
      });
    }
  }

  // -------------------------------------------------------------------------
  // Re-measure once fonts settle — masked line grouping depends on where text
  // actually wraps.
  // -------------------------------------------------------------------------
  if (window.ScrollTrigger) {
    window.addEventListener('load', function () { window.ScrollTrigger.refresh(); });
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(function () { window.ScrollTrigger.refresh(); });
    }
  }
})();
