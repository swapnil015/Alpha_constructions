/* ===========================================================================
   Alpha Concern — shared motion library
   ---------------------------------------------------------------------------
   The brief specified Framer Motion. This site is server-rendered PHP with no
   React, so Framer Motion cannot run here; GSAP + ScrollTrigger are already
   loaded site-wide and express the same motion language. These are the
   equivalents of the requested primitives:

       <MaskedText>  ->  Motion.maskedText(el)
       <FadeUp>      ->  Motion.fadeUp(els)
       <CountUp>     ->  Motion.countUp(el)
       <DrawLine>    ->  Motion.drawLine(el)
       useReveal()   ->  Motion.reveal(el, fn)

   Every timing token lives in Motion.tokens so the whole site can be tuned
   from one place. Nothing here changes colour, type, layout or spacing — the
   resting state of every element is exactly what the CSS already produces.
   =========================================================================== */

window.Motion = (function () {
  'use strict';

  // -------------------------------------------------------------------------
  // Tokens — the single source of timing truth
  // -------------------------------------------------------------------------
  var tokens = {
    // Signature easing: slow, heavy, confident. Never springy.
    ease:      'cubic-bezier(0.22, 1, 0.36, 1)',
    gsapEase:  'power4.out',          // GSAP's nearest equivalent curve
    duration: { fast: 0.7, base: 0.9, slow: 1.0, line: 1.1, draw: 1.2 },
    stagger:  { tight: 0.1, base: 0.12, cards: 0.15 },
    count:     1.6,
    // Reveals fire once, when the element is ~20% into the viewport.
    start:     'top 80%',
    // Section labels contract from this to their authored tracking.
    labelTracking: '0.35em'
  };

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var gsap = window.gsap;
  var ST   = window.ScrollTrigger;
  var ready = !!(gsap && ST) && !reduced;
  if (ready) gsap.registerPlugin(ST);

  /** Run `fn` once, when `el` is ~20% into view. Falls through immediately
      when motion is off, so content is never left hidden. */
  function reveal(el, fn) {
    if (!el) return;
    if (!ready) { fn(true); return; }
    ST.create({ trigger: el, start: tokens.start, once: true, onEnter: function () { fn(false); } });
  }

  // -------------------------------------------------------------------------
  // MaskedText — text rises from behind an invisible edge
  // -------------------------------------------------------------------------
  /**
   * Wraps each visual line in an overflow-hidden box and slides it up from
   * 100% below. Lines are detected by measuring word offsets rather than
   * assumed, so a heading that wraps to three lines animates as three.
   */
  function maskedText(el, opts) {
    if (!el || el.dataset.masked) return [];
    opts = opts || {};
    el.dataset.masked = '1';

    // 1. Wrap every word so its box can be measured.
    var html = el.innerHTML;
    var probe = document.createElement('div');
    probe.innerHTML = html;

    var words = [];
    /*
     * `inherited` carries the classes of every inline ancestor down onto the
     * word span. Words are later moved out of their wrappers and into line
     * boxes, which would otherwise strip styling — e.g. the gold italic on
     * <span class="italic-accent">that endures</span>.
     */
    (function walk(node, inherited) {
      Array.prototype.slice.call(node.childNodes).forEach(function (child) {
        if (child.nodeType === 3) {
          var parts = child.textContent.split(/(\s+)/);
          var frag = document.createDocumentFragment();
          parts.forEach(function (p) {
            if (!p.trim()) { frag.appendChild(document.createTextNode(p)); return; }
            var s = document.createElement('span');
            s.className = ('mt-w ' + inherited).trim();
            s.textContent = p;
            frag.appendChild(s);
            words.push(s);
          });
          node.replaceChild(frag, child);
        } else if (child.nodeType === 1) {
          var cls = (child.getAttribute('class') || '').trim();
          walk(child, (inherited + ' ' + cls).trim());
        }
      });
    })(probe, '');

    el.innerHTML = probe.innerHTML;
    words = Array.prototype.slice.call(el.querySelectorAll('.mt-w'));
    if (!words.length) return [];

    // 2. Group words into visual lines by their measured vertical offset.
    var lines = [];
    var currentTop = null;
    words.forEach(function (w) {
      var top = w.offsetTop;
      if (currentTop === null || Math.abs(top - currentTop) > 4) {
        lines.push([]);
        currentTop = top;
      }
      lines[lines.length - 1].push(w);
    });

    // 3. Rebuild as masked line boxes.
    var inners = [];
    var frag = document.createDocumentFragment();
    lines.forEach(function (line) {
      var mask  = document.createElement('span');
      mask.className = 'mt-line';
      var inner = document.createElement('span');
      inner.className = 'mt-line__in';
      line.forEach(function (w, i) {
        inner.appendChild(w);
        if (i < line.length - 1) inner.appendChild(document.createTextNode(' '));
      });
      mask.appendChild(inner);
      frag.appendChild(mask);
      inners.push(inner);
    });
    el.innerHTML = '';
    el.appendChild(frag);

    if (!ready) return inners;

    gsap.set(inners, { yPercent: 100 });
    reveal(opts.trigger || el, function (instant) {
      if (instant) { gsap.set(inners, { yPercent: 0 }); return; }
      gsap.to(inners, {
        yPercent: 0,
        duration: opts.duration || tokens.duration.slow,
        ease: tokens.gsapEase,
        stagger: opts.stagger != null ? opts.stagger : tokens.stagger.base,
        delay: opts.delay || 0
      });
    });
    return inners;
  }

  // -------------------------------------------------------------------------
  // FadeUp
  // -------------------------------------------------------------------------
  function fadeUp(els, opts) {
    els = toArray(els);
    if (!els.length) return;
    opts = opts || {};
    if (!ready) return;

    gsap.set(els, { opacity: 0, y: opts.y != null ? opts.y : 24 });
    reveal(opts.trigger || els[0], function (instant) {
      if (instant) { gsap.set(els, { opacity: 1, y: 0 }); return; }
      gsap.to(els, {
        opacity: 1, y: 0,
        duration: opts.duration || tokens.duration.base,
        ease: tokens.gsapEase,
        stagger: opts.stagger != null ? opts.stagger : tokens.stagger.cards,
        delay: opts.delay || 0
      });
    });
  }

  // -------------------------------------------------------------------------
  // DrawLine — the small gold divider beside a section label
  // -------------------------------------------------------------------------
  /**
   * The dividers are ::before pseudo-elements, which GSAP cannot target
   * directly, so the scale is driven through a custom property the CSS reads.
   */
  function drawLine(el, opts) {
    if (!el) return;
    opts = opts || {};
    if (!ready) return;
    gsap.set(el, { '--dash': 0 });
    reveal(opts.trigger || el, function (instant) {
      if (instant) { gsap.set(el, { '--dash': 1 }); return; }
      gsap.to(el, { '--dash': 1, duration: 0.8, ease: tokens.gsapEase, delay: opts.delay || 0 });
    });
  }

  /** Label: draw its divider, then fade in as tracking contracts. */
  function label(el, opts) {
    if (!el) return;
    opts = opts || {};
    if (!ready) return;
    var target = getComputedStyle(el).letterSpacing;
    drawLine(el, opts);
    gsap.set(el, { opacity: 0, letterSpacing: tokens.labelTracking });
    reveal(opts.trigger || el, function (instant) {
      if (instant) { gsap.set(el, { opacity: 1, letterSpacing: target }); return; }
      gsap.to(el, {
        opacity: 1, letterSpacing: target,
        duration: tokens.duration.base, ease: tokens.gsapEase,
        delay: (opts.delay || 0) + 0.15
      });
    });
  }

  // -------------------------------------------------------------------------
  // CountUp
  // -------------------------------------------------------------------------
  /** Counts 0 -> target with a fast start and slow landing, then pops the "+". */
  function countUp(el, opts) {
    if (!el) return;
    opts = opts || {};
    var target = parseFloat(el.dataset.countup || el.dataset.count || el.textContent) || 0;
    var plus = opts.plus || null;

    if (!ready) { el.textContent = target; return; }

    if (plus) gsap.set(plus, { scale: 0, transformOrigin: '50% 100%' });
    reveal(opts.trigger || el, function (instant) {
      if (instant) {
        el.textContent = target;
        if (plus) gsap.set(plus, { scale: 1 });
        return;
      }
      var obj = { v: 0 };
      gsap.to(obj, {
        v: target, duration: tokens.count, ease: 'power3.out',
        onUpdate: function () { el.textContent = Math.round(obj.v); },
        onComplete: function () {
          el.textContent = target;
          // Slight overshoot on the plus — the one place a little spring reads
          // as craft rather than bounce.
          if (plus) gsap.to(plus, { scale: 1, duration: 0.45, ease: 'back.out(2.4)' });
        }
      });
    });
  }

  // -------------------------------------------------------------------------
  // Helpers
  // -------------------------------------------------------------------------
  function toArray(x) {
    if (!x) return [];
    if (typeof x === 'string') return Array.prototype.slice.call(document.querySelectorAll(x));
    if (x.length !== undefined && !x.tagName) return Array.prototype.slice.call(x);
    return [x];
  }

  /** Gentle scroll-linked parallax. dy is total drift in px across the pass. */
  function parallax(el, dy, opts) {
    if (!el || !ready) return;
    opts = opts || {};
    gsap.to(el, {
      y: dy, ease: 'none',
      scrollTrigger: {
        trigger: opts.trigger || el,
        start: opts.start || 'top bottom',
        end: opts.end || 'bottom top',
        scrub: opts.scrub != null ? opts.scrub : 0.8,
        invalidateOnRefresh: true
      }
    });
  }

  // -------------------------------------------------------------------------
  // Site chrome — nav, CTA, header, scroll-to-top
  // -------------------------------------------------------------------------
  /**
   * Subscribe to scroll position.
   *
   * Lenis drives the page, and native `scroll` listeners do not fire reliably
   * while it is running — measured: scrollY moved 300 -> 1200 with zero native
   * scroll events. Lenis publishes its own, so bind that first and keep the
   * native listener as a fallback for when Lenis is absent (reduced motion,
   * coarse pointer, or the CDN failing). Both paths only toggle classes, so
   * receiving the callback twice is harmless.
   */
  function onScroll(cb) {
    var lenis = window.__lenis;
    if (lenis && typeof lenis.on === 'function') {
      lenis.on('scroll', function (e) {
        cb(e && typeof e.scroll === 'number' ? e.scroll : window.scrollY);
      });
    }
    window.addEventListener('scroll', function () { cb(window.scrollY); }, { passive: true });
    cb(window.scrollY);
  }

  /**
   * Opt-in masked reveal for any heading marked [data-masked].
   *
   * This is the replacement for main.js's [data-split-words], which wraps each
   * word in a block-level .split-line and therefore forces one word per line
   * regardless of the available measure. maskedText() groups words into their
   * real visual lines instead, so headings wrap naturally.
   */
  function initMasked() {
    Array.prototype.slice.call(document.querySelectorAll('[data-masked]'))
      .forEach(function (el) { maskedText(el); });
  }

  function initChrome() {
    initMasked();
    // -- Header: hide going down, return going up ---------------------------
    var header = document.querySelector('.site-header');
    if (header) {
      var last = window.scrollY;
      onScroll(function (y) {
        // main.js also owns this class, but its native listener is silenced by
        // Lenis; setting it here keeps the scrolled state working.
        header.classList.toggle('is-scrolled', y > 60);

        if (reduced) return;
        // Ignore the top of the page and tiny jitters.
        if (y > 160 && y > last + 6) header.classList.add('is-hidden');
        else if (y < last - 6) header.classList.remove('is-hidden');
        last = y;
      });
    }

    // -- Magnetic CTA with a diagonal shine sweep ---------------------------
    if (!reduced && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
      document.querySelectorAll('.nav-cta, .btn-primary').forEach(function (btn) {
        btn.classList.add('is-magnetic');
        var qx = null, qy = null;
        btn.addEventListener('mousemove', function (e) {
          if (!gsap) return;
          if (!qx) {
            qx = gsap.quickTo(btn, 'x', { duration: 0.5, ease: 'power3.out' });
            qy = gsap.quickTo(btn, 'y', { duration: 0.5, ease: 'power3.out' });
          }
          var r = btn.getBoundingClientRect();
          // Capped at 6px so the control never leaves its own bounds.
          qx(((e.clientX - (r.left + r.width / 2)) / (r.width / 2)) * 6);
          qy(((e.clientY - (r.top + r.height / 2)) / (r.height / 2)) * 6);
        });
        btn.addEventListener('mouseleave', function () {
          if (qx) { qx(0); qy(0); }
        });
      });
    }

    // -- Scroll-to-top: only past 600px -------------------------------------
    var top = document.querySelector('.floater-top');
    if (top) onScroll(function (y) { top.classList.toggle('is-visible', y > 600); });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initChrome);
  } else {
    initChrome();
  }

  return {
    tokens: tokens, ready: ready, reduced: reduced,
    reveal: reveal, maskedText: maskedText, fadeUp: fadeUp,
    drawLine: drawLine, label: label, countUp: countUp, parallax: parallax
  };
})();
