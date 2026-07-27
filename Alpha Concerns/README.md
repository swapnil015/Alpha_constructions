# Alpha Concern — Construction & Real Estate Website

Premium PHP 8.1 + MySQL website for Alpha Concern (Kathmandu, Nepal). Designed for cPanel shared/VPS hosting.

## Stack
- PHP 8.1+ (no Composer dependency required for runtime)
- MySQL 8 (via cPanel phpMyAdmin)
- Vanilla JS + Alpine.js 3 + GSAP 3 + ScrollTrigger + Lenis (CDN)
- Custom front-controller routing (no framework)

## Directory layout (cPanel)
```
/home/<cpanel-user>/
├── includes/              # Outside web root: config, db, auth, helpers
├── cvs/                   # Outside web root: CV uploads
└── public_html/           # Web root
    ├── index.php          # Front controller for public site
    ├── .htaccess          # Rewrites + security headers
    ├── assets/            # css, js, img
    ├── uploads/           # User-uploaded media (web-accessible)
    ├── api/               # Form handlers (contact, career)
    └── admin/             # Admin panel (its own front controller)
```

## Setup (cPanel)
1. Upload `/includes/` and `/cvs/` to home directory (one level above `public_html`).
2. Upload contents of `public_html/` into your `public_html/`.
3. Create MySQL database in cPanel → MySQL Databases. Create user, grant ALL privileges.
4. Import `database/schema.sql` via phpMyAdmin.
5. Edit `/includes/config.php` — set DB credentials, site URL, SMTP, etc.
6. Visit `/admin/login.php` → default credentials are seeded by `schema.sql` (change immediately).

## Default admin credentials (seeded)
- Email: `admin@alphaconcern.com`
- Password: `ChangeMe!2026`

**Change on first login.**

## Local dev
```bash
cd public_html
php -S localhost:8000
```
Then update `/includes/config.php` `BASE_URL` to `http://localhost:8000`.

## Notes
- All forms have CSRF protection.
- Login has lockout after 5 failed attempts (15 min).
- Image uploads auto-validate MIME (not extension), strip EXIF, and (when GD/Imagick available) convert to WebP with srcset variants.
- CVs stored OUTSIDE web root and served via authenticated PHP download script.
