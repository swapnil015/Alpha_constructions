/* ===========================================================================
   Alpha Concern — Why Alpha Concern, staggered glass timeline
   ---------------------------------------------------------------------------
   Six cards alternate left and right, linked by a stepped gold connector that
   draws itself as the section is scrolled, with a spark running ahead of the
   drawn edge and joints lighting as it passes.

   The connector is generated from the cards' measured positions rather than
   hard-coded, so it re-routes correctly when the layout changes breakpoint or
   the copy reflows.
   =========================================================================== */

(function () {
  'use strict';

  var section = document.querySelector('[data-why]');
  if (!section) return;

  var titleEl  = section.querySelector('[data-why-title]');
  var timeline = section.querySelector('.why__timeline');
  var rows     = Array.prototype.slice.call(section.querySelectorAll('[data-why-row]'));
  var svg      = section.querySelector('[data-why-wire]');
  var track    = section.querySelector('[data-why-track]');
  var fill     = section.querySelector('[data-why-fill]');
  var spark    = section.querySelector('[data-why-spark]');
  var joints   = section.querySelector('[data-why-joints]');
  if (!rows.length) return;

  var gsap = window.gsap;
  var ScrollTrigger = window.ScrollTrigger;
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var NS = 'http://www.w3.org/2000/svg';
  var CORNER = 14;   // bend radius on the stepped path

  // -------------------------------------------------------------------------
  // Connector geometry
  // -------------------------------------------------------------------------

  /** Where the wire meets each card: the inner edge, at mid height. */
  function anchors() {
    var t = timeline.getBoundingClientRect();
    return rows.map(function (row) {
      var card = row.querySelector('[data-why-card]');
      var c = card.getBoundingClientRect();
      var isLeft = row.classList.contains('why__row--left');
      var stacked = window.matchMedia('(max-width: 760px)').matches;
      return {
        // Stacked layout runs the wire down the left gutter instead.
        x: stacked ? 14 : (isLeft ? c.right - t.left : c.left - t.left),
        y: c.top - t.top + c.height / 2
      };
    });
  }

  /**
   * Build an orthogonal path: out from one card, along to the midpoint,
   * down, then in to the next — with rounded bends.
   */
  function buildPath(pts) {
    if (pts.length < 2) return '';
    var d = 'M ' + pts[0].x + ' ' + pts[0].y;

    for (var i = 0; i < pts.length - 1; i++) {
      var a = pts[i];
      var b = pts[i + 1];
      var mx = (a.x + b.x) / 2;

      // A stacked layout shares one x, so there is no bend to draw.
      if (Math.abs(b.x - a.x) < 1) { d += ' L ' + b.x + ' ' + b.y; continue; }

      var h1 = mx > a.x ? 1 : -1;          // direction out of card A
      var h2 = b.x > mx ? 1 : -1;          // direction into card B
      var r  = Math.min(CORNER, Math.abs(mx - a.x), Math.abs(b.x - mx), Math.abs(b.y - a.y) / 2);

      d += ' L ' + (mx - h1 * r) + ' ' + a.y;
      d += ' Q ' + mx + ' ' + a.y + ' ' + mx + ' ' + (a.y + r);
      d += ' L ' + mx + ' ' + (b.y - r);
      d += ' Q ' + mx + ' ' + b.y + ' ' + (mx + h2 * r) + ' ' + b.y;
      d += ' L ' + b.x + ' ' + b.y;
    }
    return d;
  }

  function paintJoints(pts) {
    joints.textContent = '';
    pts.forEach(function (p, i) {
      var c = document.createElementNS(NS, 'circle');
      c.setAttribute('cx', p.x);
      c.setAttribute('cy', p.y);
      c.setAttribute('r', 4.5);
      c.setAttribute('class', 'why__joint');
      c.dataset.joint = String(i);
      joints.appendChild(c);
    });
  }

  var drawTrigger = null;

  function layoutWire() {
    if (!svg || !track) return;
    var w = timeline.offsetWidth;
    var h = timeline.offsetHeight;
    svg.setAttribute('viewBox', '0 0 ' + w + ' ' + h);
    svg.setAttribute('width', w);
    svg.setAttribute('height', h);

    var pts = anchors();
    var d = buildPath(pts);
    track.setAttribute('d', d);
    fill.setAttribute('d', d);
    spark.setAttribute('d', d);
    paintJoints(pts);

    var len = fill.getTotalLength();
    fill.style.strokeDasharray = len;
    fill.style.strokeDashoffset = len;
    // A short dash chased along the same path reads as a travelling spark and
    // needs no plugin.
    spark.style.strokeDasharray = '14 ' + len;
    spark.style.strokeDashoffset = '0';
    return len;
  }

  // -------------------------------------------------------------------------
  // Static fallback
  // -------------------------------------------------------------------------
  if (!gsap || !ScrollTrigger || reduceMotion) {
    section.classList.add('is-static');
    return;
  }
  gsap.registerPlugin(ScrollTrigger);
  section.classList.add('is-live');

  // -------------------------------------------------------------------------
  // Heading — character by character
  // -------------------------------------------------------------------------
  if (titleEl) {
    var words = titleEl.textContent.trim().split(' ');
    titleEl.textContent = '';
    words.forEach(function (word, wi) {
      // Wrap per word so a line break never lands mid-word.
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

  rows.forEach(function (row, i) {
    ScrollTrigger.create({
      trigger: row,
      start: 'top 72%',
      onEnter: function () {
        row.classList.add('is-active');
        var j = joints.querySelector('[data-joint="' + i + '"]');
        if (j) j.classList.add('is-active');
      },
      onLeaveBack: function () {
        row.classList.remove('is-active');
        var j = joints.querySelector('[data-joint="' + i + '"]');
        if (j) j.classList.remove('is-active');
      }
    });
  });

  // -------------------------------------------------------------------------
  // Draw the connector
  // -------------------------------------------------------------------------
  function buildDraw() {
    var len = layoutWire();
    if (!len) return;

    if (drawTrigger) { drawTrigger.scrollTrigger && drawTrigger.scrollTrigger.kill(); drawTrigger.kill(); }

    drawTrigger = gsap.timeline({
      scrollTrigger: {
        trigger: timeline,
        start: 'top 70%',
        end: 'bottom 80%',
        scrub: 0.6,
        invalidateOnRefresh: true
      }
    })
      .fromTo(fill,  { strokeDashoffset: len }, { strokeDashoffset: 0, ease: 'none' }, 0)
      // Chase the dash the full length so the spark tracks the drawn edge.
      .fromTo(spark, { strokeDashoffset: 0 },   { strokeDashoffset: -len, ease: 'none' }, 0);
  }

  buildDraw();

  // The wire is measured from real geometry, so anything that moves the cards
  // has to rebuild it: the hero resizing itself, fonts landing, a resize.
  var resizeTimer = null;
  function rebuild() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () { buildDraw(); ScrollTrigger.refresh(); }, 150);
  }

  window.addEventListener('resize', rebuild, { passive: true });
  document.addEventListener('alpha:layout', rebuild);
  window.addEventListener('load', rebuild);
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(rebuild);
})();
