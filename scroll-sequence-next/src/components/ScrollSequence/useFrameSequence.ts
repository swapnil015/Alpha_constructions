'use client';

/**
 * useFrameSequence
 * ---------------------------------------------------------------------------
 * Scroll-driven image-sequence engine. Owns loading, decoding and canvas
 * painting; the component around it owns layout and markup.
 *
 * MEMORY MODEL — read before changing anything here.
 *
 * Frames are held as HTMLImageElements, which store the *encoded* bytes: a
 * 1280x720 JPEG is ~25 KB, so a 240-frame sequence costs ~6 MB. Decoding is
 * left to the browser, which keeps its own decode cache and — critically —
 * evicts from it under memory pressure.
 *
 * An earlier version of this hook also pinned decoded frames as ImageBitmaps
 * (up to 220 MB) on top of that browser cache. On Windows that combination
 * crashed the renderer with STATUS_ACCESS_VIOLATION. Do not reintroduce a
 * pinned decode cache. `img.decode()` warms the next few frames without taking
 * ownership of the memory, which is all this needs.
 *
 * For the same reason the 2D context does NOT use `desynchronized: true`. The
 * low-latency canvas path has a history of GPU-driver access violations on
 * Windows, and it buys nothing measurable here.
 */

import { useEffect, useRef, useState } from 'react';

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

export interface FrameSequenceOptions {
  /** Ordered, absolute frame URLs. */
  frames: string[];
  /** Scroll travel in CSS pixels allocated per frame. Higher = slower scrub. */
  pxPerFrame?: number;
  /** Clamp total travel, in viewport heights. */
  minTravelVh?: number;
  maxTravelVh?: number;
  /** Playhead easing, 0–1. 1 disables easing. Frame-rate independent. */
  smoothing?: number;
  /** Cap the canvas backing store; 2 is plenty for 1080p-or-smaller sources. */
  maxDPR?: number;
  /** Parallel image requests during the background fill. */
  concurrency?: number;
  /** Initial pass loads every Nth frame so the sequence is scrubbable sooner. */
  keyStride?: number;
  /** How many frames ahead of the playhead to pre-decode. */
  decodeAhead?: number;
  /** Called on every rAF tick with eased progress (0–1). Not React state. */
  onProgress?: (progress: number) => void;
}

export interface FrameSequenceResult {
  sceneRef: React.RefObject<HTMLDivElement | null>;
  stageRef: React.RefObject<HTMLDivElement | null>;
  canvasRef: React.RefObject<HTMLCanvasElement | null>;
  /** 0–1 during the initial keyframe pass. */
  loadProgress: number;
  /** True once the first paint has happened and the loader can lift. */
  ready: boolean;
  /** Live progress without re-rendering. Read inside rAF/effects. */
  progressRef: React.RefObject<number>;
}

type SlotState = 0 | 1 | 2 | 3; // idle · loading · ready · failed

// ---------------------------------------------------------------------------
// Hook
// ---------------------------------------------------------------------------

export function useFrameSequence(options: FrameSequenceOptions): FrameSequenceResult {
  const {
    frames,
    pxPerFrame = 15,
    minTravelVh = 2.6,
    maxTravelVh = 6.5,
    smoothing = 0.16,
    maxDPR = 2,
    concurrency = 8,
    keyStride = 8,
    decodeAhead = 6,
    onProgress,
  } = options;

  const sceneRef = useRef<HTMLDivElement | null>(null);
  const stageRef = useRef<HTMLDivElement | null>(null);
  const canvasRef = useRef<HTMLCanvasElement | null>(null);

  const progressRef = useRef(0);
  const [loadProgress, setLoadProgress] = useState(0);
  const [ready, setReady] = useState(false);

  // onProgress is read from a ref so a caller passing an inline arrow does not
  // tear down and rebuild the entire engine on every render.
  const onProgressRef = useRef(onProgress);
  onProgressRef.current = onProgress;

  const framesKey = frames.length ? `${frames.length}:${frames[0]}` : '';

  useEffect(() => {
    const scene = sceneRef.current;
    const stage = stageRef.current;
    const canvas = canvasRef.current;
    if (!scene || !stage || !canvas || frames.length === 0) return;

    // alpha:false lets the compositor skip per-pixel blending (the sequence is
    // fully opaque). No `desynchronized` — see the header comment.
    const ctx = canvas.getContext('2d', { alpha: false });
    if (!ctx) return;
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isSmall = window.matchMedia('(max-width: 720px)').matches;

    const count = frames.length;
    const netLanes = isSmall ? Math.min(concurrency, 5) : concurrency;

    // ---- state ------------------------------------------------------------
    const img: (HTMLImageElement | null)[] = new Array(count).fill(null);
    const state: SlotState[] = new Array(count).fill(0);
    const decoding = new Set<number>();
    const netQueue: number[] = [];

    let netActive = 0;
    // Natural pixel size of a frame, learned from the first decoded image.
    let srcW = 0;
    let srcH = 0;

    let travel = 0;
    let lastW = 0;
    let lastH = 0;
    let needsRedraw = true;
    let drawnIndex = 0;
    let current = 0;
    let lastTarget = 0;
    let rafId: number | null = null;
    let running = false;
    let lastT = 0;
    let disposed = false;

    // ---- loading ----------------------------------------------------------
    const load = (i: number): Promise<void> => {
      if (state[i] !== 0) return Promise.resolve();
      state[i] = 1;
      return new Promise<void>((resolve) => {
        const im = new Image();
        im.decoding = 'async';
        im.onload = () => {
          img[i] = im;
          state[i] = 2;
          if (!srcW) { srcW = im.naturalWidth; srcH = im.naturalHeight; }
          resolve();
        };
        im.onerror = () => { state[i] = 3; resolve(); };
        im.src = frames[i];
      });
    };

    const pumpNet = (): void => {
      while (netActive < netLanes && netQueue.length) {
        // Nearest the playhead first, so the fill order tracks the user.
        let best = 0;
        let bestDist = Infinity;
        for (let k = 0; k < netQueue.length; k++) {
          const d = Math.abs(netQueue[k] - drawnIndex);
          if (d < bestDist) { bestDist = d; best = k; }
        }
        const idx = netQueue.splice(best, 1)[0];
        if (state[idx] !== 0) continue;

        netActive++;
        void load(idx).then(() => {
          netActive--;
          if (!disposed) pumpNet();
        });
      }
    };

    /**
     * Warm the browser's own decode cache for the frames about to be shown.
     * This takes no ownership of the decoded memory — the browser stays free to
     * evict under pressure, which is exactly what we want.
     */
    const warmAhead = (i: number): void => {
      const hi = Math.min(count - 1, i + decodeAhead);
      for (let k = i; k <= hi; k++) {
        if (state[k] !== 2 || decoding.has(k)) continue;
        const im = img[k];
        if (!im || typeof im.decode !== 'function') continue;
        decoding.add(k);
        im.decode().catch(() => { /* best-effort */ });
      }
    };

    const maintainWindow = (i: number, settled: boolean): void => {
      const lo = Math.max(0, i - 4);
      const hi = Math.min(count - 1, i + 24);
      for (let k = lo; k <= hi; k++) {
        if (state[k] === 0 && !netQueue.includes(k)) netQueue.push(k);
      }
      pumpNet();
      // Skip decode hints during a fast scrub — those frames are already gone.
      if (settled) warmAhead(i);
    };

    /** Nearest paintable frame — prevents a blank canvas while scrubbing. */
    const resolve = (i: number): HTMLImageElement | null => {
      if (state[i] === 2) return img[i];
      for (let r = 1; r < count; r++) {
        const a = i - r;
        const b = i + r;
        if (a >= 0 && state[a] === 2) return img[a];
        if (b < count && state[b] === 2) return img[b];
      }
      return null;
    };

    // ---- layout -----------------------------------------------------------
    const layout = (): void => {
      const vh = stage.clientHeight || window.innerHeight;
      travel = Math.round(
        Math.min(Math.max(count * pxPerFrame, vh * minTravelVh), vh * maxTravelVh),
      );
      // Scene = travel + one stage height, because the sticky child occupies
      // the final viewport of the scene before it releases.
      scene.style.height = `${travel + vh}px`;
      lastW = window.innerWidth;
      lastH = vh;
    };

    const resizeCanvas = (): void => {
      const dpr = Math.min(window.devicePixelRatio || 1, maxDPR);
      // Measure the canvas, not the stage: on narrow screens CSS gives the
      // canvas a fixed-ratio band so a 16:9 render is not cropped in half.
      const rect = canvas.getBoundingClientRect();
      let w = Math.max(1, Math.round(rect.width * dpr));
      let h = Math.max(1, Math.round(rect.height * dpr));

      // Never allocate more pixels than the source frame has — upscaling a
      // 1280x720 JPEG to a 4K backing store costs memory and buys no detail.
      if (srcW && w > srcW) {
        const scale = srcW / w;
        w = srcW;
        h = Math.max(1, Math.round(h * scale));
      }

      if (canvas.width !== w || canvas.height !== h) {
        canvas.width = w;
        canvas.height = h;
        ctx.imageSmoothingEnabled = true;   // context state resets on resize
        ctx.imageSmoothingQuality = 'high';
        needsRedraw = true;
      }
    };

    // ---- painting ---------------------------------------------------------
    const paint = (src: HTMLImageElement): void => {
      const cw = canvas.width;
      const ch = canvas.height;
      const iw = src.naturalWidth;
      const ih = src.naturalHeight;
      if (!iw || !ih) return;

      const canvasRatio = cw / ch;
      const imageRatio = iw / ih;
      let sx: number, sy: number, sw: number, sh: number;

      if (imageRatio > canvasRatio) {
        sh = ih; sw = ih * canvasRatio; sx = (iw - sw) / 2; sy = 0;   // crop sides
      } else {
        sw = iw; sh = iw / canvasRatio; sx = 0; sy = (ih - sh) / 2;   // crop top/bottom
      }

      try {
        ctx.drawImage(src, sx, sy, sw, sh, 0, 0, cw, ch);
      } catch {
        // A frame that failed mid-decode can throw. Skip it rather than break
        // the loop; the next tick paints a neighbouring frame.
      }
    };

    const draw = (i: number): void => {
      const src = resolve(i);
      if (src) paint(src);
    };

    // ---- scroll → progress ------------------------------------------------
    const readProgress = (): number => {
      if (travel <= 0) return 0;
      const raw = -scene.getBoundingClientRect().top / travel;
      return raw < 0 ? 0 : raw > 1 ? 1 : raw;
    };

    // ---- loop -------------------------------------------------------------
    const tick = (now: number): void => {
      const dt = lastT ? Math.min((now - lastT) / 1000, 0.1) : 1 / 60;
      lastT = now;

      const target = readProgress();
      const speed = Math.abs(target - lastTarget) * Math.max(1, count - 1);
      const settled = speed < 2;
      lastTarget = target;

      if (reduceMotion || smoothing >= 1) {
        current = target;
      } else {
        // Exponential smoothing normalised to 60fps, so the feel is identical
        // on a 60Hz laptop and a 120Hz phone.
        const k = 1 - Math.pow(1 - smoothing, dt * 60);
        current += (target - current) * k;
        // Snap below half a frame, else the playhead creeps and never idles.
        if (Math.abs(target - current) < 0.5 / Math.max(1, count)) current = target;
      }

      progressRef.current = current;

      let i = Math.round(current * (count - 1));
      if (i < 0) i = 0;
      if (i > count - 1) i = count - 1;

      if (i !== drawnIndex || needsRedraw) {
        drawnIndex = i;
        needsRedraw = false;
        draw(i);
        maintainWindow(i, settled);
      }

      onProgressRef.current?.(current);
      rafId = requestAnimationFrame(tick);
    };

    const start = (): void => {
      if (running || disposed) return;
      running = true;
      lastT = 0;
      rafId = requestAnimationFrame(tick);
    };

    const stop = (): void => {
      if (!running) return;
      running = false;
      if (rafId !== null) cancelAnimationFrame(rafId);
      rafId = null;
    };

    // ---- boot -------------------------------------------------------------
    layout();
    resizeCanvas();

    const keys: number[] = [];
    for (let k = 0; k < count; k += keyStride) keys.push(k);
    if (keys[keys.length - 1] !== count - 1) keys.push(count - 1);

    let doneCount = 0;
    let cursor = 0;

    const nextKey = (): Promise<void> => {
      if (cursor >= keys.length || disposed) return Promise.resolve();
      const idx = keys[cursor++];
      return load(idx).then(() => {
        doneCount++;
        setLoadProgress(doneCount / keys.length);
        return nextKey();
      });
    };

    const lanes: Promise<void>[] = [];
    for (let c = 0; c < Math.min(netLanes, keys.length); c++) lanes.push(nextKey());

    void Promise.all(lanes).then(() => {
      if (disposed) return;
      // Source dimensions are known now, so the backing store can be clamped.
      resizeCanvas();

      // Paint the opening state before the loader lifts, so the stage is never
      // blank if the rAF loop happens to be parked.
      const p0 = readProgress();
      current = p0;
      lastTarget = p0;
      progressRef.current = p0;
      drawnIndex = Math.round(p0 * (count - 1));
      draw(drawnIndex);
      onProgressRef.current?.(p0);

      setReady(true);

      for (let j = 0; j < count; j++) if (state[j] === 0) netQueue.push(j);
      pumpNet();
      start();
    });

    // ---- lifecycle --------------------------------------------------------
    const io =
      'IntersectionObserver' in window
        ? new IntersectionObserver(
            (entries) => {
              if (entries[0].isIntersecting) { needsRedraw = true; start(); }
              else stop();
            },
            { rootMargin: '120px 0px' },
          )
        : null;
    io?.observe(scene);

    const onVisibility = (): void => {
      if (document.hidden) stop();
      else { needsRedraw = true; start(); }
    };
    document.addEventListener('visibilitychange', onVisibility);

    /**
     * Mobile browsers fire resize whenever the URL bar collapses. Re-running
     * layout() there would shift the scene under the user's finger mid-scroll,
     * so height-only changes are ignored unless they are large.
     */
    let resizeTimer: ReturnType<typeof setTimeout> | null = null;
    const onResize = (): void => {
      if (resizeTimer) clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        const significant =
          window.innerWidth !== lastW || Math.abs(window.innerHeight - lastH) > 140;
        if (significant) layout();
        resizeCanvas();
      }, 120);
    };
    window.addEventListener('resize', onResize, { passive: true });

    const onOrientation = (): void => {
      setTimeout(() => { layout(); resizeCanvas(); }, 260);
    };
    window.addEventListener('orientationchange', onOrientation);

    // ---- teardown ---------------------------------------------------------
    return () => {
      disposed = true;
      stop();
      io?.disconnect();
      document.removeEventListener('visibilitychange', onVisibility);
      window.removeEventListener('resize', onResize);
      window.removeEventListener('orientationchange', onOrientation);
      if (resizeTimer) clearTimeout(resizeTimer);
      // Drop image references so the browser can reclaim both the encoded
      // bytes and anything it decoded from them.
      for (let i = 0; i < count; i++) img[i] = null;
      decoding.clear();
      netQueue.length = 0;
    };
    // framesKey collapses the array identity so a caller passing a new array
    // literal with identical contents does not rebuild the engine.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    framesKey, pxPerFrame, minTravelVh, maxTravelVh, smoothing,
    maxDPR, concurrency, keyStride, decodeAhead,
  ]);

  return { sceneRef, stageRef, canvasRef, loadProgress, ready, progressRef };
}
