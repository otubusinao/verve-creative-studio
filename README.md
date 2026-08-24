# Verve Creative Studio — Project Structure

Plain PHP / MySQL / JS / CSS. No framework. XAMPP locally, FTP-deployed to IONOS Web Hosting Plus.
No build step — the folder you're looking at IS the web root that gets FTP'd to the server.

## How to read file status

Every stub `.php` file has a header comment with a `Status:` line:

- **DESIGN APPROVED** — safe to build against the referenced approved design
- **BLOCKED** — do not build layout/content yet; a visual design and/or confirmed copy is still missing (design-first rule, see project brief Section 1)
- **TODO** — infrastructure/logic file with no design dependency; can be implemented once its dependencies (DB schema, Stripe keys, etc.) are ready

Do not remove or "get ahead of" a BLOCKED status without an actual design in hand.

## Top-level map

```
/                       → Home (index.php) — APPROVED, build from assets/reference/
├── about.php           → BLOCKED
├── contact.php         → BLOCKED
├── services/           → 10 service pages, all BLOCKED (content received, design pending)
├── portfolio/          → 9 category pages + dynamic project.php, all BLOCKED
├── exhibitions/        → landing + current/past/upcoming + dynamic detail.php, all BLOCKED
├── gallery/             → browse + dynamic artwork.php, BLOCKED
├── shop/                → listing, product, cart (multi-item), checkout, enquiry (single-item) — BLOCKED
├── installations/       → service page + dynamic case-study.php, BLOCKED
├── journal/             → listing + dynamic article.php, BLOCKED
├── book/
│   ├── index.php        → service selector (10 cards), BLOCKED
│   ├── flow-a/           → Online Payment Flow (Wedding/Photography), 11-step wizard, BLOCKED
│   │   └── steps/        → one file per wizard step
│   ├── flow-b/           → Creative Project Brief — BLOCKED, brief fields not yet specified
│   ├── training/         → Training & Consultation enrolment form — BLOCKED (form confirmed, fields/design pending)
│   └── confirmation.php  → BLOCKED
├── dashboard/            → Client Dashboard (full account system — confirmed in scope), BLOCKED
├── admin/                → Admin Dashboard, all modules BLOCKED, security requirements non-negotiable regardless
├── includes/             → shared PHP (config, db, auth, functions, mailer, stripe-client, header/footer partials)
│                           — protected by .htaccess (deny direct access)
├── stripe/webhook.php    → Stripe webhook endpoint — deliberately NOT blocked by .htaccess, Stripe must reach it
├── uploads/               → user-uploaded files, PHP execution disabled via .htaccess
├── assets/
│   ├── css/, js/, images/, fonts/
│   └── reference/         → approved static HTML builds kept for traceability while converting to PHP includes
├── database/schema.sql    → single shared MySQL DB, one table set per feature, prefixed naming
├── .htaccess              → HTTPS redirect + directory listing off (root level)
└── .gitignore
```

## Key conventions carried over from the project brief

- **Single MySQL database.** Every feature's tables live in one DB, prefixed by area (`exhibitions`, `gallery_artworks`, `shop_products`, etc.) — see `database/schema.sql`.
- **No Imagick** — use PHP's GD for any thumbnailing/resizing.
- **No video files on shared hosting** — embed via unlisted YouTube/Vimeo, store only lightweight images/thumbnails.
- **Stripe** — webhook (`stripe/webhook.php`) is the source of truth for "paid" status, not the browser redirect. SSH is confirmed available on Web Hosting Plus, so the Stripe SDK via Composer is the plan (rather than raw cURL).
- **IONOS SMTP**, not PHP `mail()` — see `includes/mailer.php`.
- **Cron is available on Web Hosting Plus**, but time-dependent state (e.g. exhibition Upcoming/Current/Past) is still computed on render by default for simplicity — only reach for cron where something genuinely needs a scheduled job (e.g. reminder emails).
- **Uploads are never web-executable** — enforced via `uploads/.htaccess`, not just "stored somewhere safe."
- **`includes/config.php` holds placeholders only** — real DB/Stripe/SMTP credentials get filled in once provisioned, and this file should never be committed with real secrets (see `.gitignore`).

## Open items still blocking specific folders

- `book/flow-b/` — exact Creative Project Brief fields not yet specified by client
- `dashboard/` — full client account system confirmed in scope; needs its own schema (client_users, sessions) before building
- `shop/cart.php` + `shop/enquiry.php` — both multi-item cart and single-item/enquiry paths confirmed in scope; needs schema for both
- IONOS DB/SMTP/Stripe credentials — provisioned locally against XAMPP for now, swapped in `includes/config.php` before go-live

## Local development

Point XAMPP's `htdocs` at this folder directly (or symlink it in). No build step — edit PHP/CSS/JS and refresh. Deployment to IONOS is a straight FTP copy of this same tree.
