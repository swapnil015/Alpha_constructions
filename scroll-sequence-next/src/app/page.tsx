import { ScrollSequence, SequenceCaption } from '@/components/ScrollSequence';
import manifest from '../../public/frames/manifest.json';
import styles from './page.module.css';

/**
 * The manifest is generated at build time by scripts/generate-frame-manifest.mjs
 * (wired to `predev` / `prebuild`), so the frame list is a static import — no
 * runtime fetch, no waterfall, and it works with `output: 'export'`.
 */
const frames: string[] = manifest.frames;

/**
 * Caption windows overlap by exactly the fade length (0.045) so each hand-off
 * is a true cross-dissolve rather than a fade to nothing and back. The first
 * starts below 0 and the last ends above 1 so the opening and closing captions
 * sit at full opacity at the extremes.
 */
const CAPTIONS = [
  {
    from: -0.05, to: 0.28,
    eyebrow: 'Commercial · Kathmandu',
    title: <>The anatomy<br />of a <em>build</em></>,
    body: 'Every elevation resolved before a single foundation is poured.',
  },
  {
    from: 0.235, to: 0.545,
    eyebrow: 'The Envelope',
    title: <>Timber louvres,<br />tuned to the <em>sun</em></>,
    body: 'A vertical brise-soleil cuts western glare while keeping the floorplate daylit.',
  },
  {
    from: 0.5, to: 0.81,
    eyebrow: 'The Core',
    title: <>Eight floors,<br />one clear <em>span</em></>,
    body: 'A column-free atrium carries light from roof to ground across every level.',
  },
  {
    from: 0.765, to: 1.05,
    eyebrow: 'Delivered',
    title: <>Engineered<br />to <em>endure</em></>,
    body: 'NBC 105 seismic resilience, verified by independent peer review.',
    cta: { href: '/contact', label: 'Start your project' },
  },
];

export default function Home() {
  return (
    <>
      <header className={styles.bar}>
        <a className={styles.mark} href="/">Alpha&nbsp;Concern</a>
        <a className={styles.barLink} href="/projects">All Projects</a>
      </header>

      <ScrollSequence
        frames={frames}
        label="A commercial building model rotating through a full turn."
      >
        {CAPTIONS.map((c) => (
          <SequenceCaption key={c.eyebrow} from={c.from} to={c.to}>
            <div className={styles.eyebrow}>{c.eyebrow}</div>
            <h2 className={styles.title}>{c.title}</h2>
            <p className={styles.body}>{c.body}</p>
            {c.cta && (
              <a className={styles.cta} href={c.cta.href}>{c.cta.label}</a>
            )}
          </SequenceCaption>
        ))}
      </ScrollSequence>

      {/* Proves the page returns to normal scrolling once the sticky stage releases. */}
      <main className={styles.after}>
        <div className={styles.afterInner}>
          <div className={styles.eyebrow}>What you just scrolled through</div>
          <h2 className={styles.afterTitle}>Every project starts as a model.</h2>
          <p className={styles.afterBody}>
            Before we break ground we resolve the building completely — structure, envelope,
            services and finish — so what gets built is what was agreed.
          </p>
          <a className={styles.afterCta} href="/projects">See the built work</a>
        </div>
      </main>
    </>
  );
}
