/* ===========================================================================
   Alpha Concern — Projects listing, pop-in reveal
   ---------------------------------------------------------------------------
   Cards scale and lift into place as their group is reached, staggered across
   the row. IntersectionObserver rather than GSAP: the transition lives in CSS,
   so this file only decides when to add .is-in.
   =========================================================================== */

(function () {
  'use strict';

  var root = document.querySelector('[data-pg]');
  if (!root) return;

  var cards = Array.prototype.slice.call(root.querySelectorAll('[data-pg-card]'));
  if (!cards.length) return;

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // No observer support, or motion is off: show everything immediately. The
  // portfolio must never be left invisible because a reveal did not run.
  if (reduced || !('IntersectionObserver' in window)) {
    root.classList.add('pg--static');
    return;
  }

  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (!e.isIntersecting) return;
      var card = e.target;
      // Stagger by position within the card's own grid, so each group cascades
      // from its first card rather than continuing a page-wide count.
      var siblings = Array.prototype.slice.call(card.parentNode.children);
      card.style.transitionDelay = (siblings.indexOf(card) * 0.09) + 's';
      card.classList.add('is-in');
      io.unobserve(card);
    });
  }, { threshold: 0.18, rootMargin: '0px 0px -8% 0px' });

  cards.forEach(function (c) { io.observe(c); });

  function revealAll() {
    cards.forEach(function (c) { c.classList.add('is-in'); });
    io.disconnect();
  }

  // A card already on screen at load would otherwise wait for a scroll event.
  window.addEventListener('load', function () {
    cards.forEach(function (c) {
      var r = c.getBoundingClientRect();
      if (r.top < window.innerHeight && r.bottom > 0) c.classList.add('is-in');
    });
  });

  /*
   * Failsafe. IntersectionObserver only runs during a rendering opportunity —
   * it does not fire in a background tab, and a card can be observed but never
   * reported if the page is restored mid-scroll. A reveal that silently fails
   * leaves the entire portfolio invisible, which is far worse than losing the
   * animation, so show everything after a few seconds regardless.
   */
  setTimeout(revealAll, 3500);
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) setTimeout(revealAll, 1200);
  });
})();
