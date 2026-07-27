'use client';

/**
 * <ScrollSequence>
 * ---------------------------------------------------------------------------
 * Reusable Apple-style scroll-driven image sequence.
 *
 *   <ScrollSequence frames={frames}>
 *     <SequenceCaption from={0} to={0.3}>…</SequenceCaption>
 *   </ScrollSequence>
 *
 * Progress is published through a ref + subscriber list rather than React
 * state. A 60fps setState would re-render the whole subtree sixty times a
 * second; instead subscribers write styles directly inside the rAF tick.
 */

import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useRef,
  type CSSProperties,
  type ReactNode,
} from 'react';

import { useFrameSequence, type FrameSequenceOptions } from './useFrameSequence';
import styles from './ScrollSequence.module.css';

// ---------------------------------------------------------------------------
// Progress context
// ---------------------------------------------------------------------------

type Subscriber = (progress: number) => void;

interface SequenceContextValue {
  subscribe: (fn: Subscriber) => () => void;
  progressRef: React.RefObject<number>;
}

const SequenceContext = createContext<SequenceContextValue | null>(null);

export function useSequenceProgress(): SequenceContextValue {
  const ctx = useContext(SequenceContext);
  if (!ctx) throw new Error('useSequenceProgress must be used inside <ScrollSequence>');
  return ctx;
}

// ---------------------------------------------------------------------------
// ScrollSequence
// ---------------------------------------------------------------------------

export interface ScrollSequenceProps
  extends Omit<FrameSequenceOptions, 'onProgress'> {
  children?: ReactNode;
  className?: string;
  /** Accessible description of what the sequence depicts. */
  label?: string;
  /** Render a loading overlay until the first frame is painted. */
  showLoader?: boolean;
  /** Render the thin progress rail along the bottom of the stage. */
  showProgressRail?: boolean;
}

export function ScrollSequence({
  children,
  className,
  label = 'Scroll-driven image sequence',
  showLoader = true,
  showProgressRail = true,
  ...frameOptions
}: ScrollSequenceProps) {
  const subscribers = useRef(new Set<Subscriber>());
  const railRef = useRef<HTMLSpanElement | null>(null);

  const onProgress = useCallback((p: number) => {
    if (railRef.current) railRef.current.style.transform = `scaleX(${p})`;
    subscribers.current.forEach((fn) => fn(p));
  }, []);

  const { sceneRef, stageRef, canvasRef, loadProgress, ready, progressRef } =
    useFrameSequence({ ...frameOptions, onProgress });

  const ctxValue = useMemo<SequenceContextValue>(
    () => ({
      subscribe: (fn) => {
        subscribers.current.add(fn);
        fn(progressRef.current); // paint initial state immediately
        return () => { subscribers.current.delete(fn); };
      },
      progressRef,
    }),
    [progressRef],
  );

  const pct = Math.round(loadProgress * 100);

  return (
    <SequenceContext.Provider value={ctxValue}>
      <section
        ref={sceneRef}
        className={[styles.scene, className].filter(Boolean).join(' ')}
        aria-label={label}
      >
        <div ref={stageRef} className={styles.stage}>
          <canvas ref={canvasRef} className={styles.canvas} role="img" aria-label={label} />
          <div className={styles.scrim} aria-hidden="true" />

          {children}

          {showProgressRail && (
            <div className={styles.rail} aria-hidden="true">
              <span ref={railRef} className={styles.railFill} />
            </div>
          )}

          {showLoader && (
            <div
              className={`${styles.loader} ${ready ? styles.loaderDone : ''}`}
              role="status"
              aria-live="polite"
            >
              <div className={styles.loaderTrack}>
                <span className={styles.loaderBar} style={{ width: `${pct}%` }} />
              </div>
              <div className={styles.loaderPct}>{pct}%</div>
            </div>
          )}
        </div>
      </section>
    </SequenceContext.Provider>
  );
}

// ---------------------------------------------------------------------------
// SequenceCaption
// ---------------------------------------------------------------------------

export interface SequenceCaptionProps {
  /** Progress window, 0–1. Overlap adjacent captions by `fade` to cross-dissolve. */
  from: number;
  to: number;
  /**
   * Fade length in absolute progress units (not a fraction of the window), so
   * every transition takes the same scroll distance regardless of how long the
   * caption is on screen.
   */
  fade?: number;
  className?: string;
  style?: CSSProperties;
  children: ReactNode;
}

export function SequenceCaption({
  from,
  to,
  fade = 0.045,
  className,
  style,
  children,
}: SequenceCaptionProps) {
  const { subscribe } = useSequenceProgress();
  const ref = useRef<HTMLDivElement | null>(null);

  useEffect(() => {
    const el = ref.current;
    if (!el) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    return subscribe((p) => {
      const rise = (p - from) / fade;
      const fall = (to - p) / fade;
      let o = Math.min(rise, fall);
      o = o < 0 ? 0 : o > 1 ? 1 : o;

      el.style.opacity = o.toFixed(3);
      el.style.transform = reduceMotion
        ? 'none'
        : `translate3d(0, ${((1 - o) * 26).toFixed(2)}px, 0)`;
      // A fully transparent caption must not intercept clicks on what's under it.
      el.style.visibility = o < 0.01 ? 'hidden' : 'visible';
    });
  }, [subscribe, from, to, fade]);

  return (
    <div
      ref={ref}
      className={[styles.caption, className].filter(Boolean).join(' ')}
      style={{ opacity: 0, ...style }}
    >
      {children}
    </div>
  );
}
