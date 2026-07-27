# Alpha Concern — cPanel Deployment Guide

You have two zip files in the project root:

- `alpha-private.zip` — must be uploaded to your cPanel **home directory** (one level above `public_html`)
- `alpha-public_html.zip` — contents go **inside** `public_html/`

---

## Step 1 — Create the MySQL database

1. cPanel → **MySQL® Databases**
2. **Create New Database**, e.g. `alphaconc_main` → note the full prefixed name
3. **Create New User**, e.g. `alphaconc_admin` with a strong password — note these
4. **Add User To Database** → grant **ALL PRIVILEGES**

---

## Step 2 — Import the schema

1. cPanel → **phpMyAdmin** → select the new database
2. Click the **Import** tab
3. Upload `database/schema.sql` (the **MySQL** one, not `schema_sqlite.sql`)
4. Click **Go**. You should see ~14 tables created and seeded.

---

## Step 3 — Upload files

### Private files (above web root)
1. cPanel → **File Manager**
2. Navigate to your **home directory** (the parent of `public_html`)
3. Upload `alpha-private.zip` → right-click → **Extract** here
4. You should now have `/home/<your-user>/includes/`, `/home/<your-user>/cvs/`, `/home/<your-user>/database/`, `/home/<your-user>/views/`

### Public files (web root)
1. Open `public_html/`
2. Upload `alpha-public_html.zip` → **Extract** here
3. Confirm `public_html/index.php`, `public_html/.htaccess`, `public_html/admin/`, `public_html/api/`, `public_html/assets/`, `public_html/uploads/`, `public_html/setup.php` all exist

---

## Step 4 — Configure

Edit `~/includes/config.php` via cPanel File Manager → right-click → **Edit**:

```php
define('BASE_URL', 'https://alphaconcern.com');     // your real domain, no trailing slash

define('DB_DRIVER', 'mysql');                        // leave as 'mysql'
define('DB_HOST',   'localhost');                    // usually localhost on cPanel
define('DB_NAME',   'alphaconc_main');               // from Step 1
define('DB_USER',   'alphaconc_admin');              // from Step 1
define('DB_PASS',   'your-strong-password');         // from Step 1

define('ADMIN_EMAIL', 'info@alphaconcern.com');
```

For email, configure SMTP later (see Step 7).

---

## Step 5 — Set the admin password

1. Visit `https://alphaconcern.com/setup.php`
2. You should see **Database: connected** in green
3. Set your admin email + password (≥10 chars). Click **Set Password**
4. **Delete `setup.php`** from File Manager (very important — don't skip)
5. Sign in at `https://alphaconcern.com/admin/login`

---

## Step 6 — Permissions check

Most cPanel hosts handle this automatically, but if you see upload errors:

| Folder | Permission |
|---|---|
| `public_html/uploads/` and all subdirs | `755` (dirs) / `644` (files) |
| `~/cvs/` | `700` (dir) / `600` (files) — **outside web root** |
| `~/includes/` | `755` |
| `~/includes/config.php` | `600` (read-only to you) |

In File Manager: right-click → **Change Permissions**.

---

## Step 7 — SMTP (so contact form emails actually send)

PHP's built-in `mail()` is unreliable on shared hosting. To use SMTP:

1. Download PHPMailer: https://github.com/PHPMailer/PHPMailer/releases (latest source zip)
2. Extract and upload these three files into `~/includes/PHPMailer/`:
   - `src/PHPMailer.php`
   - `src/SMTP.php`
   - `src/Exception.php`
3. In `~/includes/config.php`:
   ```php
   define('SMTP_ENABLED', true);
   define('SMTP_HOST', 'mail.alphaconcern.com');   // or smtp.gmail.com etc
   define('SMTP_PORT', 587);
   define('SMTP_USER', 'noreply@alphaconcern.com');
   define('SMTP_PASS', 'app-password-or-mailbox-password');
   define('SMTP_FROM_EMAIL', 'noreply@alphaconcern.com');
   define('SMTP_FROM_NAME',  'Alpha Concern');
   define('SMTP_SECURE', 'tls');                    // 'tls' or 'ssl'
   ```
4. Test: submit the contact form → check that you receive both notifications.

**Recommended:** create a dedicated `noreply@alphaconcern.com` mailbox in cPanel → Email Accounts, and use those credentials.

---

## Step 8 — SSL & HTTPS redirect

1. cPanel → **SSL/TLS Status** → **Run AutoSSL** for `alphaconcern.com` and `www.alphaconcern.com`. Wait for green padlocks.
2. Once verified, edit `public_html/.htaccess` and **uncomment the HTTPS block** at the top:
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
   ```
3. Optionally also uncomment the HSTS header at the bottom of the security headers section after a week of confirmed HTTPS stability.

---

## Step 9 — Configure the site via admin

Sign in at `/admin/login` and walk through:

1. **Site Settings → Company / Social** — phone, WhatsApp, email, address, social URLs, Google Maps embed
2. **Site Settings → Homepage** — upload a hero image, edit headline/subhead, set stat numbers
3. **Site Settings → SEO & Analytics** — paste your GA4 ID, Search Console verification token
4. **Projects** — review the 4 seeded projects, upload real photos, edit Imperial Apartment content, delete placeholders if unwanted
5. **Blog** — review/replace seeded posts
6. **Site Settings → Team / Testimonials** (via DB or future admin module — currently DB only) — replace `[Bracketed]` placeholders with real names

---

## Step 10 — SEO checklist

- [ ] Visit `/sitemap.xml` — confirm it lists all your pages
- [ ] Visit `/robots.txt` — confirm contents look right
- [ ] Google Search Console → add property → verify via the meta token from Settings → SEO
- [ ] Submit sitemap at `https://alphaconcern.com/sitemap.xml`
- [ ] PageSpeed Insights test — aim for 90+ desktop / 80+ mobile

---

## Files to remove from the deploy after first-run

- `public_html/setup.php` — **delete after Step 5**
- `database/schema_sqlite.sql` — only used for local dev, harmless if left but irrelevant on production

---

## Troubleshooting

| Symptom | Likely cause |
|---|---|
| 500 error on every page | `config.php` DB credentials wrong, or schema not imported |
| Pages load but no styling | `.htaccess` rewrite not working — confirm `mod_rewrite` is enabled (it is on virtually all cPanel hosts) |
| Contact form succeeds but no email arrives | SMTP not configured (Step 7) |
| Admin login lockout | Visit `/setup.php` again to reset (then delete it) |
| Upload fails ("permissions") | Check Step 6 |
| `pdo_mysql not loaded` | Ask host to enable; standard on every cPanel host I've seen |

---

## After-launch maintenance

- Database backups: cPanel → **Backup Wizard** → schedule monthly DB exports
- Update `BASE_URL` in `config.php` if the domain ever changes
- Rotate the admin password every 90 days
- If you ever need to wipe and reimport: drop all tables in phpMyAdmin → re-import `schema.sql` → run `setup.php` again to set a fresh admin password
