/**
 * Build-time frame manifest generator.
 *
 * Scans public/frames and writes public/frames/manifest.json listing every
 * image in natural sort order. Runs from `predev` and `prebuild`, so dropping a
 * new sequence into public/frames is the only step required — no code change,
 * no hard-coded frame count.
 *
 * A build-time manifest (rather than a runtime route handler) keeps the
 * component compatible with `output: 'export'` and any static host.
 */

import { readdir, writeFile } from 'node:fs/promises';
import { join, dirname, extname } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT       = join(dirname(fileURLToPath(import.meta.url)), '..');
const FRAMES_DIR = join(ROOT, 'public', 'frames');
const OUT        = join(FRAMES_DIR, 'manifest.json');
const ALLOWED    = new Set(['.jpg', '.jpeg', '.png', '.webp', '.avif']);

/** Natural sort: frame-2 before frame-10, with or without zero padding. */
const collator = new Intl.Collator(undefined, { numeric: true, sensitivity: 'base' });

async function main() {
  let entries;
  try {
    entries = await readdir(FRAMES_DIR);
  } catch {
    console.warn('[frames] public/frames not found — writing an empty manifest.');
    await writeFile(OUT, JSON.stringify({ count: 0, frames: [] }, null, 2));
    return;
  }

  const frames = entries
    .filter((name) => ALLOWED.has(extname(name).toLowerCase()))
    .sort(collator.compare)
    .map((name) => `/frames/${encodeURIComponent(name)}`);

  await writeFile(OUT, JSON.stringify({ count: frames.length, frames }, null, 2));
  console.log(`[frames] manifest.json — ${frames.length} frames`);
}

main().catch((err) => {
  console.error('[frames] manifest generation failed:', err);
  process.exit(1);
});
