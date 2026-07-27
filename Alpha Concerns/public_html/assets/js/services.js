/* ===========================================================================
   Alpha Concern — Services, pinned cinematic sequence
   ---------------------------------------------------------------------------
   The section pins for one viewport per service. Scroll position selects which
   service is active; the transition itself is a discrete ~1s power4.out
   timeline rather than a scrubbed one, so the motion always plays at its
   designed pace no matter how fast the wheel is turned.

   Mobile does not pin at all — the same markup becomes a horizontal snap
   scroller in CSS, and this file only wires the rail to it.
   =========================================================================== */

(function () {
  'use strict';

  var section = document.querySelector('[data-svc]');
  if (!section) return;

  var gsap = window.gsap;
  var ScrollTrigger = window.ScrollTrigger;

  var stage    = section.querySelector('[data-svc-stage]');
  var wrap     = section.querySelector('[data-svc-items]');
  var items    = Array.prototype.slice.call(section.querySelectorAll('[data-svc-item]'));
  var dots     = Array.prototype.slice.call(section.querySelectorAll('[data-svc-dot]'));
  var railFill = section.querySelector('[data-svc-railfill]');
  var cursor   = section.querySelector('[data-svc-cursor]');
  if (!stage || !items.length) return;

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // -------------------------------------------------------------------------
  // Split each description into words.
  // Wrapping words (rather than measured lines) keeps this cheap and immune to
  // re-wrapping on resize; staggered tightly it reads as a line-by-line wipe.
  // -------------------------------------------------------------------------
  items.forEach(function (item) {
    var p = item.querySelector('[data-svc-desc]');
    if (!p) return;
    var words = p.textContent.trim().split(/\s+/);
    p.textContent = '';
    words.forEach(function (w, i) {
      var span = document.createElement('span');
      span.className = 'svc__word';
      span.textContent = w;
      p.appendChild(span);
      // The separator must be a text node BETWEEN the spans. A trailing space
      // inside an inline-block is collapsed away, which runs every word
      // together.
      if (i < words.length - 1) p.appendChild(document.createTextNode(' '));
    });
  });

  var parts = items.map(function (item) {
    return {
      el:     item,
      num:    item.querySelector('.svc__num'),
      title:  item.querySelector('.svc__title'),
      words:  Array.prototype.slice.call(item.querySelectorAll('.svc__word')),
      cta:    item.querySelector('.svc__cta'),
      figure: item.querySelector('[data-svc-figure]'),
      img:    item.querySelector('img'),
      media:  item.querySelector('[data-svc-media]')
    };
  });

  // Without GSAP the CSS fallback already shows every service stacked and
  // readable, so there is nothing to do.
  if (!gsap || !ScrollTrigger) { section.classList.add('is-static'); return; }
  gsap.registerPlugin(ScrollTrigger);
  section.classList.add('is-live');

  var current = 0;
  var kenBurns = null;

  function setActive(i) {
    items.forEach(function (el, k) { el.classList.toggle('is-active', k === i); });
    dots.forEach(function (d, k) { d.classList.toggle('is-active', k === i); });
  }

  /** Slow continuous zoom on whichever image is on screen. */
  function startKenBurns(p) {
    if (kenBurns) kenBurns.kill();
    if (reduceMotion) return;
    gsap.set(p.img, { scale: 1 });
    kenBurns = gsap.to(p.img, { scale: 1.08, duration: 9, ease: 'none' });
  }

  /**
   * Transition to service `i`. Outgoing content settles back and fades; the
   * incoming image rises as it scales down to rest, and the text follows in
   * sequence — number, title, description, CTA.
   */
  function go(i, instant) {
    if (i === current && !instant) return;
    var from = parts[current];
    var to   = parts[i];
    current  = i;
    setActive(i);

    if (reduceMotion || instant) {
      gsap.set([from.el, to.el], { clearProps: 'opacity' });
      gsap.set(to.figure, { yPercent: 0, scale: 1, opacity: 1 });
      gsap.set([to.num, to.title, to.cta], { y: 0, opacity: 1 });
      gsap.set(to.words, { y: 0, opacity: 1 });
      startKenBurns(to);
      return;
    }

    var D = 1.0;
    var E = 'power4.out';

    if (from !== to) {
      gsap.to(from.figure, { scale: 0.94, opacity: 0, duration: 0.75, ease: 'power2.inOut', overwrite: true });
      gsap.to([from.num, from.title, from.cta], { y: -26, opacity: 0, duration: 0.5, ease: 'power2.inOut', overwrite: true });
      gsap.to(from.words, { y: -14, opacity: 0, duration: 0.4, ease: 'power2.inOut', overwrite: true });
    }

    var tl = gsap.timeline({ defaults: { ease: E, force3D: true } });

    tl.fromTo(to.figure,
      { yPercent: 9, scale: 1.05, opacity: 0 },
      { yPercent: 0, scale: 1, opacity: 1, duration: D, onComplete: function () { startKenBurns(to); } }, 0);

    tl.fromTo(to.num,   { y: 40, opacity: 0 }, { y: 0, opacity: 1, duration: D * 0.9 }, 0.05);
    tl.fromTo(to.title, { y: 46, opacity: 0 }, { y: 0, opacity: 1, duration: D },       0.12);
    tl.fromTo(to.words, { y: 18, opacity: 0 },
      { y: 0, opacity: 1, duration: 0.7, stagger: 0.012 }, 0.24);
    tl.fromTo(to.cta,   { y: 24, opacity: 0 }, { y: 0, opacity: 1, duration: 0.8 },     0.42);
  }

  // -------------------------------------------------------------------------
  // Desktop / tablet: pin and step. Mobile: CSS snap scroller, no pin.
  // -------------------------------------------------------------------------
  gsap.matchMedia().add(
    { pinned: '(min-width: 861px)', flow: '(max-width: 860px)' },
    function (ctx) {
      if (ctx.conditions.flow) {
        // Horizontal scroller — mirror the active card into the rail.
        var onScroll = function () {
          var i = Math.round(wrap.scrollLeft / Math.max(1, wrap.clientWidth * 0.88));
          i = Math.max(0, Math.min(items.length - 1, i));
          if (i !== current) { current = i; setActive(i); }
          var max = wrap.scrollWidth - wrap.clientWidth;
          if (railFill) railFill.style.transform = 'scaleY(' + (max > 0 ? wrap.scrollLeft / max : 0).toFixed(3) + ')';
        };
        wrap.addEventListener('scroll', onScroll, { passive: true });
        go(0, true);
        return function () { wrap.removeEventListener('scroll', onScroll); };
      }

      go(0, true);

      var st = ScrollTrigger.create({
        trigger: section,
        start: 'top top',
        // One viewport per service, plus a little tail so the last one is read
        // before the section releases.
        end: function () { return '+=' + Math.round(window.innerHeight * (items.length + 0.25)); },
        pin: stage,
        pinSpacing: true,
        anticipatePin: 1,
        invalidateOnRefresh: true,
        onUpdate: function (self) {
          var i = Math.floor(self.progress * items.length);
          if (i > items.length - 1) i = items.length - 1;
          if (i < 0) i = 0;
          go(i);
          if (railFill) railFill.style.transform = 'scaleY(' + self.progress.toFixed(4) + ')';
        }
      });

      // Rail numbers jump the user to a service.
      var onDot = dots.map(function (dot, k) {
        var fn = function () {
          var y = st.start + (st.end - st.start) * ((k + 0.5) / items.length);
          window.scrollTo({ top: y, behavior: 'smooth' });
        };
        dot.addEventListener('click', fn);
        return fn;
      });

      return function () {
        st.kill();
        dots.forEach(function (d, k) { d.removeEventListener('click', onDot[k]); });
      };
    }
  );

  // -------------------------------------------------------------------------
  // Mouse parallax + "Explore" cursor over the imagery.
  // Parallax lives on .svc__media so it never fights the entrance tween on
  // .svc__figure or the Ken Burns scale on the <img>.
  // -------------------------------------------------------------------------
  if (!reduceMotion &&
      window.matchMedia('(pointer: fine)').matches &&
      window.matchMedia('(min-width: 861px)').matches) {

    var quickX = null, quickY = null, activeMedia = null;

    stage.addEventListener('mousemove', function (e) {
      var media = parts[current].media;
      if (!media) return;
      var r = media.getBoundingClientRect();
      var inside = e.clientX >= r.left && e.clientX <= r.right &&
                   e.clientY >= r.top  && e.clientY <= r.bottom;

      if (inside) {
        if (activeMedia !== media) {
          activeMedia = media;
          quickX = gsap.quickTo(media, 'x', { duration: 0.8, ease: 'power3.out' });
          quickY = gsap.quickTo(media, 'y', { duration: 0.8, ease: 'power3.out' });
        }
        var dx = (e.clientX - (r.left + r.width  / 2)) / (r.width  / 2);
        var dy = (e.clientY - (r.top  + r.height / 2)) / (r.height / 2);
        quickX(dx * 14);
        quickY(dy * 14);
        media.classList.add('is-hover');
        if (cursor) {
          cursor.classList.add('is-visible');
          gsap.to(cursor, { x: e.clientX, y: e.clientY, duration: 0.35, ease: 'power3.out' });
        }
      } else if (activeMedia) {
        quickX(0); quickY(0);
        activeMedia.classList.remove('is-hover');
        activeMedia = null;
        if (cursor) cursor.classList.remove('is-visible');
      }
    }, { passive: true });

    stage.addEventListener('mouseleave', function () {
      if (activeMedia) { quickX(0); quickY(0); activeMedia.classList.remove('is-hover'); activeMedia = null; }
      if (cursor) cursor.classList.remove('is-visible');
    });
  }

  // The hero sequence sets its own height once its frames resolve, which moves
  // everything below it.
  document.addEventListener('alpha:layout', function () { ScrollTrigger.refresh(); });
  window.addEventListener('load', function () { ScrollTrigger.refresh(); });
})();
