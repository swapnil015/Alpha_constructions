/* ===========================================================================
   Alpha Concern — Why Alpha Concern, staggered glass timeline
   ---------------------------------------------------------------------------
   Heading reveals character by character; cards rise individually as they are
   reached; a gold line draws itself down the section with a dot running ahead
   of the fill, activating each node as it passes.
   =========================================================================== */

(function () {
  'use strict';

  var section = document.querySelector('[data-why]');
  if (!section) return;

  var titleEl  = section.querySelector('[data-why-title]');
  var lineFill = section.querySelector('[data-why-line]');
  var dot      = section.querySelector('[data-why-dot]');
  var rows     = Array.prototype.slice.call(section.querySelectorAll('[data-why-row]'));
  var timeline = section.querySelector('.why__timeline');
  if (!rows.length) return;

  var gsap = window.gsap;
  var ScrollTrigger = window.ScrollTrigger;
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Without GSAP the CSS fallback already shows everything in place.
  if (!gsap || !ScrollTrigger || reduceMotion) { section.classList.add('is-static'); return; }
  gsap.registerPlugin(ScrollTrigger);
  section.classList.add('is-live');

  // -------------------------------------------------------------------------
  // Heading — character by character
  // -------------------------------------------------------------------------
  if (titleEl) {
    var words = titleEl.textContent.trim().split(' ');
    titleEl.textContent = '';

    words.forEach(function (word, wi) {
      // Wrap each word so a line break never lands mid-word.
      var wordEl = document.createElement('span');
      wordEl.className = 'why__word';
      word.split('').forEach(function (ch) {
        var span = document.createElement('span');
        span.className = 'why__char';
        span.textContent = ch;
        wordEl.appendChild(span);
      });
      titleEl.appendChild(wordEl);
      if (wi < words.length - 1) titleEl.appendChild(document.createTextNode(' '));
    });

    gsap.from(titleEl.querySelectorAll('.why__char'), {
      yPercent: 100, opacity: 0,
      duration: 1.2, ease: 'power4.out', stagger: 0.022,
      scrollTrigger: { trigger: titleEl, start: 'top 86%', once: true }
    });
  }

  gsap.from(section.querySelectorAll('.why__eyebrow, .why__statement'), {
    y: 22, opacity: 0, duration: 1, ease: 'power4.out', stagger: 0.12,
    scrollTrigger: { trigger: section.querySelector('.why__head'), start: 'top 84%', once: true }
  });

  // -------------------------------------------------------------------------
  // Cards
  // -------------------------------------------------------------------------
  gsap.from(rows.map(function (r) { return r.querySelector('[data-why-card]'); }), {
    opacity: 0, y: 80, scale: 0.95,
    duration: 1, ease: 'power3.out', stagger: 0.15,
    scrollTrigger: { trigger: timeline, start: 'top 78%', once: true }
  });

  // Each row lights its node once it is genuinely in view, rather than all six
  // firing off one trigger.
  rows.forEach(function (row) {
    ScrollTrigger.create({
      trigger: row,
      start: 'top 72%',
      onEnter:     function () { row.classList.add('is-active'); },
      onLeaveBack: function () { row.classList.remove('is-active'); }
    });
  });

  // -------------------------------------------------------------------------
  // Connecting line + travelling dot
  // -------------------------------------------------------------------------
  if (lineFill) {
    gsap.fromTo(lineFill, { scaleY: 0 }, {
      scaleY: 1, ease: 'none', transformOrigin: 'top center',
      scrollTrigger: {
        trigger: timeline,
        start: 'top 68%',
        end: 'bottom 78%',
        scrub: 0.6,
        invalidateOnRefresh: true
      }
    });
  }

  if (dot) {
    gsap.fromTo(dot, { yPercent: 0 }, {
      // Expressed against the dot's own height so it tracks the line no matter
      // how tall the timeline becomes at a given breakpoint.
      y: function () { return timeline.offsetHeight; },
      ease: 'none',
      scrollTrigger: {
        trigger: timeline,
        start: 'top 68%',
        end: 'bottom 78%',
        scrub: 0.6,
        invalidateOnRefresh: true
      }
    });
  }

  // The hero sequence resizes itself after its frames resolve, which moves
  // everything below it.
  document.addEventListener('alpha:layout', function () { ScrollTrigger.refresh(); });
  window.addEventListener('load', function () { ScrollTrigger.refresh(); });
})();
