# Portfolio — PHP + MySQL CMS

A single-page portfolio website with an admin dashboard. Every piece of content on the
site — text, images, projects, services, testimonials, resume entries and contact
details — is stored in MySQL and edited from the dashboard. No code changes needed to
update the site.

---

## 1. Requirements

- PHP 8.0 or newer with the `pdo_mysql` extension
- MySQL 5.7 / 8.x (or MariaDB)
- Apache with `.htaccess` support (Laragon, XAMPP or standard cPanel hosting)

---

## 2. Installation

**Step 1 — put the files in place**

Copy the project folder into your web root (`D:\laragon\www\`, `C:\xampp\htdocs\`, or
`public_html` on live hosting).

**Step 2 — set the database credentials**

Open `api/config.php` and fill in the `DB_*` values. On Laragon the defaults already
work (`root`, empty password).

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'portfolio');
define('DB_USER', 'root');
define('DB_PASS', '');
```

**Step 3 — run the installer**

Visit `install.php` in the browser once, for example `http://portfolio.test/install.php`.

It creates the database and tables, migrates any old JSON data, and seeds the default
content. Running it a second time changes nothing that already exists.

**Step 4 — delete the installer**

Once it reports success, delete `install.php`. Leaving it on a live server lets anyone
else run it.

---

## 3. Configuration — `api/config.php`

This is the only file you need to edit by hand.

### Admin login

```php
define('ADMIN_EMAIL',    'you@example.com');
define('ADMIN_PASSWORD', 'ChangeMe123!');   // <-- change this first
```

### Email (contact form)

```php
define('SMTP_USER', 'you@example.com');   // your email
define('SMTP_PASS', '');                  // App Password goes here
```

**Creating a Gmail App Password**

1. Go to https://myaccount.google.com/security and turn on **2-Step Verification**
2. Open https://myaccount.google.com/apppasswords and create a new app password
3. Paste the 16-digit code into `SMTP_PASS` (remove the spaces)

> A regular Gmail password will not work — Google disabled that.

**For providers other than Gmail** (Hostinger, cPanel, Outlook) just change the host and port:

```php
define('SMTP_HOST',   'smtp.hostinger.com');
define('SMTP_PORT',   465);
define('SMTP_SECURE', 'ssl');
```

**If you hit an SSL/TLS error on localhost:**

```php
define('SMTP_VERIFY_CERT', false);
```

> Set this back to `true` on live hosting.

If `SMTP_PASS` is left empty, the site still works — contact messages are stored in the
dashboard, they are just not emailed.

---

## 4. The admin dashboard

Sign in at `/admin/`. There are eight tabs:

| Tab | What it controls |
|---|---|
| **Projects** | Portfolio items: title, category, image, live link, display order |
| **Categories** | The filter tabs shown above the portfolio grid |
| **About** | About heading and text, your photo, the skill pills, the counters |
| **Services** | Section text, plus each service card (title, description, icon) |
| **Testimonials** | Section text, plus each testimonial (name, job title, stars, photo, quote) |
| **Resume** | Section text, plus the Education and Experience timeline entries |
| **Settings** | Site title, hero name, menu labels, portfolio text, phone / email / address |
| **Messages** | Everything submitted through the contact form |

Notes:

- The **"All"** portfolio tab is generated automatically and always comes first.
- **Order** uses lower numbers first (1, 2, 3…). Leave it empty and it is assigned automatically.
- When editing an item, leaving the image field empty keeps the existing image.
- A category that still has projects cannot be deleted — move or delete those projects first.
- Messages are stored whether or not the email goes out, so nothing is ever lost.

---

## 5. File structure

```
index.php                 homepage (pulls everything from the database)
install.php               one-time database setup — delete after running

includes/
  header.php              <head> and the left sidebar
  footer.php              closing markup and scripts
  helpers.php             section headings, project cards, resume columns
  sections/               about, portfolio, services, testimonials, resume, contact

admin/
  index.php               login
  dashboard.php           shell: tabs, stats, shared form helpers
  actions.php             every save and delete action
  panels/                 one file per dashboard tab
  admin.css

api/
  config.php              >>> THE ONLY FILE YOU EDIT <<<
  db.php                  PDO connection and all data access
  lib.php                 escaping, validation, image uploads
  auth.php                sessions, CSRF, login throttling
  contact.php             contact form handler
  smtp.php                email sending (no external library required)
  projects.php            public JSON endpoint (unused by the site itself)

assets/
  css/style.css           site styles
  css/animations.css      every animation
  js/main.js              portfolio tabs, contact form
  js/animations.js        scroll reveals, counters, scrollspy

uploads/                  uploaded images, one subfolder per content type
data/                     legacy JSON files, kept only as a backup
backup/                   the original static index.html
```

---

## 6. Backup

Two things matter:

1. **The database** — export it from phpMyAdmin or with
   `mysqldump -u root portfolio > portfolio.sql`
2. **The `uploads/` folder** — every uploaded image lives there

---

## 7. Security notes

- `api/config.php` stores the password in plain text. An `.htaccess` rule blocks the file
  from the browser, but **change the password as soon as the site goes live**.
- `data/` is blocked by `.htaccess` (on Apache). If your host runs Nginx, ask support to
  block that folder.
- `uploads/.htaccess` prevents any script from executing inside the uploads folder, and
  covers every subfolder.
- Dashboard panels refuse to run unless they are loaded through `dashboard.php`.
- Five failed logins trigger a five-minute lockout.
- All forms are CSRF protected, and the contact form has a honeypot field plus a
  30-second rate limit.

---

## 8. Known gaps

- **The site is not responsive yet.** `assets/css/style.css` has no media queries, so the
  layout breaks on phones and tablets.
