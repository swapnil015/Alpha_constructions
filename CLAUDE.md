# Alpha Concern — project instructions

A construction and real-estate development website for **Alpha Concern Pvt.
Ltd.**, Kathmandu, Nepal. This file is loaded automatically at the start of a
Claude Code session in this folder. Read `HANDOFF.md` next for what is
in flight right now.

---

## Scope

Work only inside `D:\Alpha`. Nothing outside it belongs to this project — in
particular `D:\claude` is an unrelated client (OSRC) and must not be touched.

## Stack and layout

PHP 8.2, no framework — a custom front controller with its own router.
MySQL in production, SQLite for local development. Alpine.js, GSAP 3.12.5 +
ScrollTrigger, and Lenis, all from CDN. No build step for the PHP site.

```
Alpha Concerns/
  includes/          bootstrap, config, helpers, auth   ← lives ABOVE the web root in production
  views/             page templates
  database/          schema.sql (MySQL), schema_sqlite.sql, seeds, preview.sqlite
  cvs/               job applicants' CVs — personal data, gitignored, above web root
  public_html/       the web root
    assets/js/       main.js, motion.js, sequence.js + one file per section
    assets/css/      main.css (single stylesheet, ~3000 lines)
    admin/           the admin console
  router.dev.php     dev-only shim: forces SQLite, adds what Apache does natively
build-static.sh      crawls the dev server into dist/ for the Vercel demo
dist/                that static export — regenerate, never hand-edit
```

## Running it

```bash
php -S localhost:8080 -t "Alpha Concerns/public_html" "Alpha Concerns/router.dev.php"
```

`.claude/launch.json` defines this as the `alpha-concern` preview. `router.dev.php`
sets `ALPHA_DB=sqlite` so local work never needs MySQL.

## Deploying — read this before saying anything is "done"

**A git push does not deploy.** The live site is
[alphaconcerns.com](https://alphaconcerns.com), on cPanel shared hosting, and
it pulls nothing from GitHub. Changed files under `public_html/` must be
uploaded separately (cPanel File Manager, SFTP, or scp — SSH 22 and FTP 21 are
both open on that host).

Verify a deploy by fetching the file from the live origin and grepping for a
string unique to the change. Do not trust the `?v=` query parameter alone.

Cache-busting is automatic: `asset()` versions URLs by `filemtime`, so
uploading a file changes its `?v=` and browsers refetch. No cache clearing.

Vercel (`alpha-constructions.vercel.app`) is a **client demo only** — Vercel
cannot run PHP, so `build-static.sh` crawls the dev server into `dist/`.
cPanel is the real host.

## Git

Branch `main`, remote https://github.com/swapnil015/Alpha_constructions.
Commit and push after each self-contained change without being asked. One
commit per logical change. Credentials are cached in the Windows credential
store; `gh` is not installed.

## Admin console

`/admin/login`, by **email** — the seeded account is `admin@alphaconcern.com`
(singular, unlike the site domain). The password in `README.md` verifies
against the local SQLite database but **not** against the live host, where it
was changed during deployment.

`auth_attempt()` returns the identical `'Invalid credentials.'` for an unknown
email and for a wrong password, so diagnose with
`SELECT id, email FROM admin_users;` rather than by guessing. Five failed
attempts lock the account for fifteen minutes — never probe the live login to
test a password. Hashes are bcrypt cost 12.

Recovery is a phpMyAdmin `UPDATE` setting `password_hash` alongside
`failed_attempts = 0, locked_until = NULL`. `public_html/setup.php` used to do
this without authentication; it has been deleted from the live host and should
stay deleted.

---

## Hard-won constraints — changing these has broken the site before

**`sequence.js` memory model.** The homepage hero is a 240-frame canvas
sequence. Frames stay as `HTMLImageElement`s holding *encoded* bytes. An
earlier version also pinned decoded frames as `ImageBitmap`s on top of
Chrome's own decode cache, which crashed the renderer on Windows with
`STATUS_ACCESS_VIOLATION`. Do not reintroduce a pinned decode cache, and do
not set `desynchronized: true` on the context. The file's header comment says
the same thing at more length.

**Drive Lenis from exactly one clock.** `lenis.raf(t)` derives its delta from
`t - lastT`, and in lerp mode that feeds `1 - Math.exp(-60 * lerp * delta)`.
Feeding it both a `requestAnimationFrame` loop and `gsap.ticker` makes the
deltas alternate sign, because the two clocks have different origins. A
negative delta drives that factor past 1 in the negative direction and throws
the scroll position away from its target every frame. That was a visible
page-wide shudder on the live site for days.

**Do not put a static `will-change` on split-text spans.** One span per word
or character means dozens of permanently promoted compositor layers per
headline; the homepage was carrying 199 of 720 elements that way. GSAP
promotes transform/opacity tweens for their duration anyway, and the character
reveals are `once: true`.

**Lenis CDN URL.** `lenis@1.0.42` does not exist on npm — that version shipped
as `@studio-freight/lenis@1.0.42`. The wrong URL fails silently and smooth
scrolling is simply dead sitewide.

**`$page_scripts` needs `partial('footer', compact('page_scripts'))`.** `view()`
uses `extract()` inside a function, so a bare variable never reaches the
footer.

**Two tweens, two elements.** The recurring pattern in the section scripts is
an outer element carrying parallax and an inner one carrying the entrance, so
that two tweens never fight over one transform matrix.

**GSAP `fromTo` for filters.** A computed `filter` of `none` interpolates as
`brightness(0)`, which snapped a project video to black mid-scroll. State the
start explicitly.

## Content rules

- **Never fabricate.** No invented testimonials, client names, statistics or
  contact details. Real project data came from
  `Alpha_Concern_Company_Profile.pdf`; contact details come from the database.
- **No staff personal data.** The previous site exposed personal mobile
  numbers, personal Gmail addresses, home localities and WhatsApp numbers for
  team members. None of that was imported and none of it should be.
- **CVs and uploads are gitignored** — applicants' CVs are personal data and
  live above the web root in production.

## Asset conventions

Photographs ship as JPEG at quality 84, converted from the client's PNGs with
GD at native resolution — that has consistently turned 1.7–2.4 MB into
175–330 KB. Background videos are transcoded muted with ffmpeg. Hero images
use `<img class="hero--shot__img">` rather than a CSS background, so they keep
alt text, `fetchpriority` and intrinsic dimensions for the preload scanner;
per-page crops are set with `object-position` on a page-specific class.

Check title contrast over any new hero photograph before committing — the
established floor for this site is well above 4.5:1, and existing heroes
measure 12–14:1.
