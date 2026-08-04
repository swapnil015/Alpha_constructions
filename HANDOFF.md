# Handoff — state of play

**Last updated:** 2026-07-30 · last commit `2d82a6f`

Read `CLAUDE.md` first for how the project is built and the constraints that
have broken it before. This file is only "where we left off" and goes stale —
update it when you finish something.

---

## Do this first

**Two files are fixed in the repo but not yet on the live server.** The owner
reported the site flickering and lagging while scrolling; both causes are
found, fixed and verified locally, and neither is live yet.

Upload to `public_html/` on the cPanel host:

| From (repo) | To (server) |
| --- | --- |
| `Alpha Concerns/public_html/assets/js/main.js` | `public_html/assets/js/main.js` |
| `Alpha Concerns/public_html/assets/css/main.css` | `public_html/assets/css/main.css` |

`alphaconcerns-scroll-fix.zip` in this folder holds exactly those two, already
at the right paths — extract it into `public_html/` and it lands correctly.

**Confirm it worked** by loading the site and checking that `main.js` contains
the string `DRIVE LENIS FROM EXACTLY ONE CLOCK`. If it doesn't, the upload did
not take. A `?v=` change alone is not proof.

### What those two fixes are

- `36d79fe` — Lenis was being advanced by both a `requestAnimationFrame` loop
  and `gsap.ticker`. Two clocks with different origins, so the deltas
  alternated sign and the scroll position was thrown off target every frame.
  That was the flicker.
- `2d82a6f` — 199 of the homepage's 720 elements carried a static
  `will-change`, one compositor layer per animated word and character. Down to
  67. That was the lag.

Both are explained at more length in `CLAUDE.md` under "Hard-won constraints".

---

## Known and still open

**Backdrop blur is the next lag suspect.** Ten elements use `backdrop-filter`,
six of them `.why__card` at `blur(16px) saturate(1.15)`. Backdrop blur
re-reads and re-blurs the region beneath it whenever anything under it moves —
during scroll, every frame. The sticky header also blurs over the hero canvas,
which repaints continuously. This was left alone deliberately: reducing it
changes a look the owner approved, so it is his call. Profile against the live
site with the two fixes actually running before touching it.

**Admin lockout.** As of the last session the owner could not sign in to
`/admin` and the account was locked. See the admin section of `CLAUDE.md` for
the phpMyAdmin recovery — the fix is one `UPDATE` that resets the hash and
clears `failed_attempts` / `locked_until` together.

**No deploy credentials are stored anywhere in this repo**, by design. The
hosting was set up by a colleague who holds them.

**Three copies of the 240-frame sequence** (~21 MB each) sit in the repo under
the deploy folders. Harmless but worth trimming.

**No real testimonials.** The homepage sections for them were removed rather
than filled with invented quotes. If the client supplies real ones they can
come back.

---

## Recently finished

Heroes across the interior pages now use the client's own photographs —
Careers, Insights, Contact, Projects, Why Us, and About, plus the About "Our
Story" plate. Each was converted from PNG to JPEG at quality 84 and given a
page-specific `object-position`, because a single shared crop cut the subject
out of several of them. `/why-us`, `/blog`, `/careers` and `/contact` were
rebuilt earlier in the run.
