# ScrollSequence — Apple-style scroll-driven image sequence for Next.js

A reusable, dependency-free React component that scrubs a rendered image
sequence with scroll position, the way Apple's iPhone and AirPods product pages
do. TypeScript, Canvas, Retina-aware, memory-budgeted.

```tsx
import { ScrollSequence, SequenceCaption } from '@/components/ScrollSequence';
import manifest from '../../public/frames/manifest.json';

<ScrollSequence frames={manifest.frames} label="Product rotating through a full turn.">
  <SequenceCaption from={-0.05} to={0.28}>
    <h2>The anatomy of a build</h2>
  </SequenceCaption>
</ScrollSequence>
```

## Getting started

```bash
npm install
npm run dev
```

`predev` and `prebuild` run `scripts/generate-frame-manifest.mjs`, which scans
`public/frames` and writes `manifest.json` in natural sort order. Drop a new
sequence in and it is picked up — no frame count to update, no naming
convention to match.

## Adding your own frames

1. Put the images in `public/frames/` (`.jpg`, `.png`, `.webp`, `.avif`).
2. Run `npm run manifest` (or just `npm run dev`).

Any consistent naming works — `frame_0001.png`, `0001.jpg`, `ezgif-frame-001.jpg`
— because sorting is natural, not lexicographic.

## How it works

**Frames stay encoded; the browser owns decoding.** Every frame is held as an
`HTMLImageElement`, which stores the *encoded* bytes — a 1280×720 JPEG is
~25 KB, so 240 frames cost ~6 MB. Decoded pixels live in the browser's own
image cache, which evicts under memory pressure. Painting falls back to the
nearest already-loaded frame, so fast scrubbing never shows a blank canvas.

> **Do not add a pinned decode cache.** An earlier version held a moving window
> of `ImageBitmap`s (up to 220 MB) on top of the browser's cache. On Windows
> that combination crashed the renderer with `STATUS_ACCESS_VIOLATION`.
> `img.decode()` warms upcoming frames without taking ownership of the memory,
> which is all this needs. For the same reason the 2D context does **not** use
> `desynchronized: true` — the low-latency canvas path has a history of
> GPU-driver access violations on Windows and buys nothing measurable here.

**The backing store is clamped to the source resolution.** Upscaling a
1280×720 frame to a 4K backing store costs memory and adds no detail, so
`resizeCanvas()` caps width at the frame's natural width.

**Decode hints are suppressed during scrubs.** `img.decode()` is called only
for the next few frames, and only while the playhead is moving slower than
~2 frames/tick — decoding frames the user is about to scroll straight past is
pure waste.

**Loading is progressive.** The first pass fetches every 8th frame, so the
sequence is fully scrubbable in a fraction of the total download. The remainder
streams in behind the user, always nearest-playhead-first.

**Progress does not go through React state.** A 60fps `setState` would re-render
the subtree sixty times a second. Progress is published through a ref plus a
subscriber list; `SequenceCaption` writes `opacity` and `transform` directly
inside the rAF tick.

**The loop parks itself** when the scene leaves the viewport
(`IntersectionObserver`) or the tab is hidden.

## Props

| Prop | Default | Notes |
|---|---|---|
| `frames` | — | Ordered frame URLs. Required. |
| `pxPerFrame` | `15` | Scroll travel per frame. Higher = slower scrub. |
| `minTravelVh` / `maxTravelVh` | `2.6` / `6.5` | Clamp total travel in viewport heights. |
| `smoothing` | `0.16` | Playhead easing, 0–1. `1` disables. Frame-rate independent. |
| `maxDPR` | `2` | Backing-store cap. |
| `decodeAhead` | `6` | Frames pre-decoded ahead of the playhead. |
| `concurrency` | `8` | Parallel image requests. |
| `keyStride` | `8` | Initial pass loads every Nth frame. |
| `showLoader` | `true` | Loading overlay until first paint. |
| `showProgressRail` | `true` | Thin progress line along the stage. |

`SequenceCaption` takes `from`, `to` and `fade` (default `0.045`). **Overlap
adjacent windows by exactly `fade`** so hand-offs cross-dissolve instead of
fading to nothing and back.

## Theming

The component styles layout only. Colour comes from CSS variables:

```css
:root {
  --sequence-bg: #2b2b2d;          /* matches the backdrop baked into frames */
  --sequence-accent: #c8922a;
  --sequence-loader-bg: #003030;
  --sequence-gutter: clamp(1.25rem, 4vw, 3.5rem);
  --sequence-mobile-ratio: 3 / 2;
}
```

## Portrait viewports

A 16:9 sequence cover-fitted into a portrait phone loses more than half its
width — it cuts the subject in two. Below 720px the canvas becomes a
fixed-ratio band (3:2 by default) with captions beneath it, and the hook sizes
the backing store from the canvas element rather than the stage, so DPR stays
correct in both modes.

## Production notes

- Frames are served with `Cache-Control: immutable` (see `next.config.mjs`).
- Compress the sequence before shipping. 1280×720 JPEG at ~25 KB/frame is a
  good target; 240 frames ≈ 6 MB.
- Keep lighting, camera and background identical across frames — only the
  subject should change. Any drift reads as flicker during the scrub.
- 30fps export is the usual sweet spot between smoothness and payload.
