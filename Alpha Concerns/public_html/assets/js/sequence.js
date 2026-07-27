/* ===========================================================================
   Alpha Concern — Scroll-Driven Hero Sequence
   ---------------------------------------------------------------------------
   Scrubs a rendered image sequence with scroll position, Apple product-page
   style. Home page only. No dependencies.

   MEMORY MODEL — read before changing anything here.

   Frames are held as HTMLImageElements, which store the *encoded* bytes: a
   1280x720 JPEG is ~25 KB, so all 240 cost ~6 MB. Decoding is left to the
   browser, which keeps its own decode cache and — critically — evicts from it
   under memory pressure.

   An earlier version of this file also pinned decoded frames as ImageBitmaps
   (up to 220 MB) on top of that browser cache. On Windows that combination
   crashed the renderer with STATUS_ACCESS_VIOLATION. Do not reintroduce a
   pinned decode cache. `img.decode()` warms the next few frames without taking
   ownership of the memory, which is all this needs.

   For the same reason the 2D context does NOT use `desynchronized: true`. The
   low-latency canvas path has a history of GPU-driver access violations on
   Windows, and it buys nothing measurable here.
   =========================================================================== */

(function () {
  'use strict';

  var CONFIG = {
    manifestUrl: '/api/frames.php',

    pxPerFrame:  15,     // scroll travel per frame; higher = slower scrub
    minTravelVh: 2.6,
    maxTravelVh: 6.0,

    smoothing:   0.16,   // playhead easing; 1 disables. See Lenis note below.
    maxDPR:      2,
    concurrency: 8,
    keyStride:   8,      // first pass loads every Nth frame
    decodeAhead: 6,      // frames to warm ahead of the playhead
    capFade:     0.045   // caption cross-dissolve length, in progress units
  };

  // -------------------------------------------------------------------------
  // DOM
  // -------------------------------------------------------------------------
  var scene = document.querySelector('[data-seq]');
  if (!scene) return;                       // not the home page

  var stage  = scene.querySelector('[data-seq-stage]');
  var canvas = scene.querySelector('[data-seq-canvas]');
  var loader = scene.querySelector('[data-seq-loader]');
  var barEl  = scene.querySelector('[data-seq-bar]');
  var pctEl  = scene.querySelector('[data-seq-pct]');
  var railEl = scene.querySelector('[data-seq-progress]');
  var hintEl = scene.querySelector('[data-seq-hint]');
  var capEls = Array.prototype.slice.call(scene.querySelectorAll('[data-seq-cap]'));
  if (!stage || !canvas) return;

  // alpha:false lets the compositor skip per-pixel blending (the sequence is
  // fully opaque). No `desynchronized` — see the header comment.
  var ctx = canvas.getContext('2d', { alpha: false });
  if (!ctx) return;
  ctx.imageSmoothingEnabled = true;
  ctx.imageSmoothingQuality = 'high';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var isSmall      = window.matchMedia('(max-width: 720px)').matches;
  if (isSmall) CONFIG.concurrency = 5;

  // -------------------------------------------------------------------------
  // Frame store
  // -------------------------------------------------------------------------
  // state: 0 idle · 1 loading · 2 ready · 3 failed
  var frames = { urls: [], count: 0, img: [], state: [] };

  var netQueue  = [];
  var netActive = 0;
  var decoding  = Object.create(null);

  // Natural pixel size of a frame, learned from the first decoded image. Used
  // to stop the canvas allocating more pixels than the source actually has.
  var srcW = 0;
  var srcH = 0;

  function discover() {
    return fetch(CONFIG.manifestUrl, { cache: 'default' })
      .then(function (r) { if (!r.ok) throw new Error('manifest ' + r.status); return r.json(); })
      .then(function (d) {
        if (!d || !d.frames || !d.frames.length) throw new Error('empty manifest');
        return d.frames;
      })
      .catch(function () { return []; });
  }

  function load(i) {
    if (frames.state[i] !== 0) return Promise.resolve();
    frames.state[i] = 1;
    return new Promise(function (resolve) {
      var im = new Image();
      im.decoding = 'async';
      im.onload = function () {
        frames.img[i] = im;
        frames.state[i] = 2;
        if (!srcW) { srcW = im.naturalWidth; srcH = im.naturalHeight; }
        resolve();
      };
      im.onerror = function () { frames.state[i] = 3; resolve(); };
      im.src = frames.urls[i];
    });
  }

  function pumpNet() {
    while (netActive < CONFIG.concurrency && netQueue.length) {
      // Nearest the playhead first, so the fill order tracks the user rather
      // than running blindly front to back.
      var best = 0, bestDist = Infinity;
      for (var k = 0; k < netQueue.length; k++) {
        var d = Math.abs(netQueue[k] - drawnIndex);
        if (d < bestDist) { bestDist = d; best = k; }
      }
      var idx = netQueue.splice(best, 1)[0];
      if (frames.state[idx] !== 0) continue;

      netActive++;
      load(idx).then(function () { netActive--; pumpNet(); });
    }
  }

  /**
   * Ask the browser to decode the next few frames ahead of time. This only
   * warms the browser's own decode cache — it takes no ownership and the
   * browser stays free to evict under pressure, which is exactly what we want.
   */
  function warmAhead(i) {
    var hi = Math.min(frames.count - 1, i + CONFIG.decodeAhead);
    for (var k = i; k <= hi; k++) {
      if (frames.state[k] !== 2 || decoding[k]) continue;
      var im = frames.img[k];
      if (!im || typeof im.decode !== 'function') continue;
      decoding[k] = true;
      im.decode().catch(function () { /* decode is best-effort */ });
    }
  }

  /** Keep the network window fed and warm what is about to be shown. */
  function maintainWindow(i, settled) {
    var lo = Math.max(0, i - 4);
    var hi = Math.min(frames.count - 1, i + 24);
    for (var k = lo; k <= hi; k++) {
      if (frames.state[k] === 0 && netQueue.indexOf(k) === -1) netQueue.push(k);
    }
    pumpNet();
    // Skip decode hints during a fast scrub — those frames are already gone.
    if (settled) warmAhead(i);
  }

  /** Nearest paintable frame — prevents a blank canvas while scrubbing. */
  function resolveFrame(i) {
    if (frames.state[i] === 2) return frames.img[i];
    for (var r = 1; r < frames.count; r++) {
      var a = i - r, b = i + r;
      if (a >= 0 && frames.state[a] === 2) return frames.img[a];
      if (b < frames.count && frames.state[b] === 2) return frames.img[b];
    }
    return null;
  }

  // -------------------------------------------------------------------------
  // Layout
  // -------------------------------------------------------------------------
  var travel = 0, lastW = 0, lastH = 0, needsRedraw = true, drawnIndex = 0;

  function layout() {
    var vh = stage.clientHeight || window.innerHeight;
    travel = Math.round(Math.min(
      Math.max(frames.count * CONFIG.pxPerFrame, vh * CONFIG.minTravelVh),
      vh * CONFIG.maxTravelVh
    ));
    // Scene = travel + one stage height, because the sticky child occupies the
    // final viewport of the scene before it releases.
    scene.style.height = (travel + vh) + 'px';
    lastW = window.innerWidth;
    lastH = vh;

    // This changes total document height, which invalidates every scroll
    // position measured further down the page. Anything scroll-driven below
    // the hero must re-measure — see story.js.
    document.dispatchEvent(new CustomEvent('alpha:layout'));
  }

  function resizeCanvas() {
    var dpr = Math.min(window.devicePixelRatio || 1, CONFIG.maxDPR);
    // Measure the canvas, not the stage: on narrow screens CSS gives the canvas
    // a fixed-ratio band so a 16:9 render is not cropped in half by portrait.
    var rect = canvas.getBoundingClientRect();
    var w = Math.max(1, Math.round(rect.width  * dpr));
    var h = Math.max(1, Math.round(rect.height * dpr));

    // Never allocate more pixels than the source frame has — upscaling a
    // 1280x720 JPEG to a 4K backing store costs memory and buys no detail.
    if (srcW && w > srcW) {
      var scale = srcW / w;
      w = srcW;
      h = Math.max(1, Math.round(h * scale));
    }

    if (canvas.width !== w || canvas.height !== h) {
      canvas.width = w; canvas.height = h;
      ctx.imageSmoothingEnabled = true;    // context state resets on resize
      ctx.imageSmoothingQuality = 'high';
      needsRedraw = true;
    }
  }

  // -------------------------------------------------------------------------
  // Painting — cover fit, centre crop
  // -------------------------------------------------------------------------
  function paint(src) {
    var cw = canvas.width, ch = canvas.height;
    var iw = src.naturalWidth, ih = src.naturalHeight;
    if (!iw || !ih) return;

    var cr = cw / ch, ir = iw / ih, sx, sy, sw, sh;
    if (ir > cr) { sh = ih; sw = ih * cr; sx = (iw - sw) / 2; sy = 0; }
    else         { sw = iw; sh = iw / cr; sx = 0; sy = (ih - sh) / 2; }

    try {
      ctx.drawImage(src, sx, sy, sw, sh, 0, 0, cw, ch);
    } catch (e) {
      // A frame that failed mid-decode can throw. Skip it rather than break
      // the whole loop; the next tick will paint a neighbouring frame.
    }
  }

  function draw(i) { var s = resolveFrame(i); if (s) paint(s); }

  // -------------------------------------------------------------------------
  // Overlay
  // -------------------------------------------------------------------------
  var caps = capEls.map(function (el) {
    return { el: el, from: parseFloat(el.dataset.in) || 0, to: parseFloat(el.dataset.out) || 1 };
  });

  function updateCaptions(p) {
    for (var k = 0; k < caps.length; k++) {
      var c = caps[k];
      // Fade length is absolute, not a fraction of the window, so every
      // transition takes the same scroll distance. Windows overlap by exactly
      // this much, making each hand-off a true 50/50 cross-dissolve.
      var o = Math.min((p - c.from) / CONFIG.capFade, (c.to - p) / CONFIG.capFade);
      o = o < 0 ? 0 : (o > 1 ? 1 : o);

      c.el.style.opacity   = o.toFixed(3);
      c.el.style.transform = reduceMotion ? 'none'
        : 'translate3d(0,' + ((1 - o) * 26).toFixed(2) + 'px,0)';
      // A transparent caption must not swallow clicks on the one beneath it.
      c.el.style.visibility = o < 0.01 ? 'hidden' : 'visible';
      c.el.setAttribute('aria-hidden', o < 0.01 ? 'true' : 'false');
    }
  }

  var hintHidden = false;
  function updateChrome(p) {
    if (railEl) railEl.style.transform = 'scaleX(' + p.toFixed(4) + ')';
    if (hintEl && !hintHidden && p > 0.012) { hintEl.classList.add('is-hidden'); hintHidden = true; }
  }

  // -------------------------------------------------------------------------
  // Scroll → progress
  // -------------------------------------------------------------------------
  function progress() {
    if (travel <= 0) return 0;
    var raw = -scene.getBoundingClientRect().top / travel;
    return raw < 0 ? 0 : (raw > 1 ? 1 : raw);
  }

  // -------------------------------------------------------------------------
  // Loop
  // -------------------------------------------------------------------------
  var current = 0, lastTarget = 0, rafId = null, running = false, lastT = 0;

  function tick(now) {
    var dt = lastT ? Math.min((now - lastT) / 1000, 0.1) : 1 / 60;
    lastT = now;

    var target = progress();
    // Frames of travel this tick. Above ~2 the user is scrubbing rather than
    // reading, and decode hints are suppressed until they slow down.
    var speed   = Math.abs(target - lastTarget) * Math.max(1, frames.count - 1);
    var settled = speed < 2;
    lastTarget = target;

    if (reduceMotion || CONFIG.smoothing >= 1) {
      current = target;
    } else {
      // Exponential smoothing normalised to 60fps, so the feel is identical on
      // a 60Hz laptop and a 120Hz phone.
      var k = 1 - Math.pow(1 - CONFIG.smoothing, dt * 60);
      current += (target - current) * k;
      // Snap below half a frame, else the playhead creeps and never idles.
      if (Math.abs(target - current) < 0.5 / Math.max(1, frames.count)) current = target;
    }

    var i = Math.round(current * (frames.count - 1));
    if (i < 0) i = 0; else if (i > frames.count - 1) i = frames.count - 1;

    if (i !== drawnIndex || needsRedraw) {
      drawnIndex = i; needsRedraw = false;
      draw(i);
      maintainWindow(i, settled);
    }

    updateCaptions(current);
    updateChrome(current);

    rafId = requestAnimationFrame(tick);
  }

  function start() { if (running) return; running = true; lastT = 0; rafId = requestAnimationFrame(tick); }
  function stop()  { if (!running) return; running = false; if (rafId) cancelAnimationFrame(rafId); rafId = null; }

  // -------------------------------------------------------------------------
  // Boot
  // -------------------------------------------------------------------------
  function setLoader(done, total) {
    var pct = total ? Math.round((done / total) * 100) : 0;
    if (barEl) barEl.style.width = pct + '%';
    if (pctEl) pctEl.textContent = pct;
  }

  discover().then(function (urls) {
    if (!urls.length) {
      // No frames deployed — fall back to the flat brand background rather
      // than leaving an empty black stage.
      scene.classList.add('seq--unavailable');
      if (loader) loader.classList.add('is-done');
      return;
    }

    frames.urls  = urls;
    frames.count = urls.length;
    for (var i = 0; i < frames.count; i++) { frames.state[i] = 0; frames.img[i] = null; }

    // Lenis already applies a long, slow easing to scroll position. Adding our
    // own on top of it reads as lag, so defer to Lenis when it is driving.
    if (window.Lenis && window.matchMedia('(pointer: fine)').matches) CONFIG.smoothing = 1;

    layout();
    resizeCanvas();

    // Pass 1 — keyframes only. Enough to make the whole sequence scrubbable in
    // a fraction of the total download; the gaps stream in behind the user.
    var keys = [];
    for (var k = 0; k < frames.count; k += CONFIG.keyStride) keys.push(k);
    if (keys[keys.length - 1] !== frames.count - 1) keys.push(frames.count - 1);

    var done = 0, cursor = 0;
    setLoader(0, keys.length);

    function next() {
      if (cursor >= keys.length) return Promise.resolve();
      var idx = keys[cursor++];
      return load(idx).then(function () { done++; setLoader(done, keys.length); return next(); });
    }

    var lanes = [];
    for (var c = 0; c < Math.min(CONFIG.concurrency, keys.length); c++) lanes.push(next());

    return Promise.all(lanes).then(function () {
      // Source dimensions are known now, so the backing store can be clamped.
      resizeCanvas();

      // Paint the opening state before the loader lifts. Captions must be set
      // here and not left to the first tick: if the loop is parked (tab hidden,
      // or a restored scroll position past the hero) the tick may not run for
      // some time and the hero would sit blank.
      var p0 = progress();
      current = p0;
      lastTarget = p0;
      drawnIndex = Math.round(p0 * (frames.count - 1));
      draw(drawnIndex);
      updateCaptions(p0);
      updateChrome(p0);

      scene.classList.add('is-ready');
      if (loader) loader.classList.add('is-done');

      // Pass 2 — background fill.
      for (var j = 0; j < frames.count; j++) if (frames.state[j] === 0) netQueue.push(j);
      pumpNet();
      start();
    });
  });

  // -------------------------------------------------------------------------
  // Lifecycle
  // -------------------------------------------------------------------------
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (e) {
      if (e[0].isIntersecting) { needsRedraw = true; start(); } else stop();
    }, { rootMargin: '120px 0px' }).observe(scene);
  }

  document.addEventListener('visibilitychange', function () {
    if (document.hidden) stop(); else { needsRedraw = true; start(); }
  });

  /**
   * Mobile browsers fire resize every time the URL bar collapses. Re-running
   * layout() there would shift the scene under the user's finger mid-scroll,
   * so height-only changes are ignored unless they are large.
   */
  var resizeTimer = null;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      if (window.innerWidth !== lastW || Math.abs(window.innerHeight - lastH) > 140) layout();
      resizeCanvas();
    }, 120);
  }, { passive: true });

  window.addEventListener('orientationchange', function () {
    setTimeout(function () { layout(); resizeCanvas(); }, 260);
  });

  window.addEventListener('pageshow', function () { needsRedraw = true; current = progress(); });
})();
