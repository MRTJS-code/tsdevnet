# Professional Profile and Recruiter Portal (PHP 8 + MySQL 8)

## Modular homepage model
- Hero is fixed and driven by `site_settings` plus the headshot document.
- Footer/contact/CV is fixed and driven by `site_settings` plus the CV/social document links.
- Everything in the middle of the homepage is now driven by ordered `homepage_modules`.
- `module_rich_text_sections` stores inline payloads for rich text / CTA-info modules.
- Structured payload data still lives in typed tables for experience, certifications, technology groups, portfolio items, and testimonials.
- `content_blocks` and `content_items` remain in the repo only as legacy generic CMS tables and are no longer the canonical homepage driver.
- Rule-based assistant knowledge remains DB-managed via `assistant_knowledge`.
- Admin auth remains DB-backed via `admin_users`.

Minimal, production-ready skeleton with magic-link auth, Turnstile CAPTCHA, basic CRM tables, and a demo chat UI.

## Prerequisites
- PHP 8+ with PDO MySQL, curl/openssl enabled.
- MySQL 8+.
- Web server (Apache/Nginx) pointing document root to `public/`.
- SMTP relay accessible at `localhost` (or adjust config).

## Setup
1. Copy `config/config.example.php` to `config/config.php` and fill values:
   - `app_url` (e.g., `https://portal.example.com`)
   - DB credentials
   - Turnstile site/secret keys
   - Mail sender + SMTP settings
   - Seed admin email/password for the initial admin bootstrap script
   - Set `session_secure=true` when HTTPS is enabled.
2. Import the schema:
   ```sh
   mysql -u USER -p -h HOST tsdevnet < migrations/001_init.sql
   ```
3. (Recommended) Install PHPMailer:
   ```sh
   composer require phpmailer/phpmailer
   ```
   Ensure `vendor/autoload.php` is uploaded with the app. If PHPMailer is missing, the Mailer falls back to `mail()`.

4. Deploy files; point web root to `public/`. Keep `config.php` **out of version control**.
5. Run the full schema and bootstrap sequence:
   ```sh
   mysql -u USER -p -h HOST DB_NAME < migrations/002_phase1a_indexes.sql
   mysql -u USER -p -h HOST DB_NAME < migrations/003_phase1b_content_cms.sql
   mysql -u USER -p -h HOST DB_NAME < migrations/004_phase1c_homepage_typed_content.sql
   mysql -u USER -p -h HOST DB_NAME < migrations/005_profile_content_model.sql
   mysql -u USER -p -h HOST DB_NAME < migrations/006_homepage_layout_refinements.sql
   mysql -u USER -p -h HOST DB_NAME < migrations/007_homepage_modules.sql
   php scripts/seed_phase1b.php
   php scripts/reset_content.php
   php scripts/seed_reusable_content.php
   ```
6. Reusable content flow:
   - `scripts/reset_content.php` clears homepage/profile/CMS content tables only.
   - `scripts/seed_reusable_content.php` reloads committed generic test/demo content.
   - `scripts/seed_profile_template.php` is kept as a compatibility wrapper around `scripts/seed_reusable_content.php`.
7. For a local/private Tony deployment only:
   ```sh
   copy scripts\\local\\reset_tony_content.php.example scripts\\local\\reset_tony_content.php
   copy scripts\\local\\seed_tony_profile.php.example scripts\\local\\seed_tony_profile.php
   php scripts\\local\\reset_tony_content.php
   php scripts\\local\\seed_tony_profile.php
   ```
   Keep copied `scripts/local/*.php` files out of version control.

## Homepage content model
- Fixed hero payload
- Ordered homepage modules for the middle of the page
- Fixed footer/contact/CV payload

Initial module types:
- rich text / executive summary
- experience timeline
- certifications
- technology groups
- featured portfolio
- testimonials
- CTA / info block

Admin flow:
- `/admin/homepage-hero.php` manages the fixed hero.
- `/admin/homepage-modules.php` manages ordered middle-page modules.
- `/admin/homepage-footer.php` and `/admin/homepage-documents.php` manage the fixed footer/contact/documents.

## Turnstile
- Create a Turnstile site + secret key at Cloudflare.
- Add keys to `config.php` under `turnstile`.
- Widgets are rendered on signup/login via the JS snippet; server-side verification enforces pass/fail.

## SMTP
- Default points to `localhost:25` with no auth.
- Set `smtp_username`/`smtp_password` and `smtp_secure` (`tls`/`ssl`) if required.
- Sender defaults to `noreply@example.com` and a generic site-admin sender name.

## Environments
- Set `app_env=dev` to expose magic links on-screen after signup/login (for setup/testing only).
- In prod, ensure HTTPS is on and `session_secure=true`.

## Auth flow
- Signup: creates `users` row with `status=pending`, emails magic link (15 min TTL).
- Login: always shows generic success; if user exists and not blocked, sends new magic link.
- Verify: hashes token, checks expiry/used/blocked, marks used, logs in, updates `last_login_at`.
- Logout: clears session.

## App area
- `/app/index.php` shows tier based on status:
  - pending -> Demo Access (5 msgs/day)
  - approved -> Full Access (50 msgs/day)
- `/app/chat.php` enforces CSRF, origin, JSON content-type, and per-user daily limits.
- Messages + conversations are stored; replies are canned in `ChatService::generateReply()`. Swap this out to call Azure OpenAI later (keep limits/logging intact).

## Admin
- Admin auth is database-backed via `admin_users`.
- `/admin/index.php` lists pending users and links to the homepage CMS areas.
- `/admin/user.php?id=...` shows recruiter details, notes, conversations/messages.

## Adding Azure OpenAI later
1. Implement `ChatService::generateReply()` to call Azure OpenAI (gpt-4o, etc.).
2. Keep rate limiting, tier logic, and logging as-is.
3. Store full request/response transcripts in MySQL (messages table already captures this).
4. Do **not** expose secrets client-side; load them from `config.php`.

## Security notes
- Sessions: HttpOnly, SameSite=Lax, Secure (when HTTPS).
- Tokens: random bytes, stored as SHA-256 hashes, single-use, 15 min expiry.
- No email enumeration on login.
- Turnstile verified server-side; fail closed.
- CSRF: session token for chat API and admin actions.
- Rate limiting stored in DB by scope/user/IP.
- Security headers set in `src/bootstrap.php` via `Util::sendSecurityHeaders()`.
- Use HTTPS in production and protect `config.php`/migrations from web access.

## File map
- Public entry: `public/index.php`, `signup.php`, `login.php`, `verify.php`, `logout.php`
- App: `public/app/index.php`, `public/app/chat.php`
- Admin: `public/admin/index.php`, `public/admin/user.php`
- Assets: `public/assets/css/styles.css`, `public/assets/js/app.js`, `public/favicon.svg`
- Core: `src/*.php`
- Config: `config/config.php` (private), sample at `config/config.example.php`
- DB: `migrations/001_init.sql`

## Running locally
- Serve `public/` via PHP's built-in server for quick checks:
  ```sh
  php -S localhost:8000 -t public
  ```
- Ensure MySQL + Turnstile keys are set; in `dev` you can view magic links directly after signup/login.
# Phase 1A Refactor Notes

This repository now runs on a lightweight layered PHP structure aimed at traditional low-cost PHP/LAMP hosting:

- `public/` entry points remain simple and hosting-friendly
- `src/Support/` centralises bootstrap, env/config loading, DB, sessions, security headers, Turnstile, mail, and view rendering
- `src/Repositories/` contains MySQL access
- `src/Services/` contains auth, magic link, approval, chat, audit, and rate limit logic
- `src/Chat/` provides the rule-based provider seam for later AI integration
- `src/Guards/` protects user and admin routes
- `views/` contains reusable server-rendered templates

## Quick setup

1. Copy `.env.example` to `.env` and set DB, app URL, mail, Turnstile, and admin values.
2. Optionally copy `config/config.example.php` to `config/config.php` for local overrides that stay off git.
3. Run:
   ```sh
   mysql -u USER -p -h HOST DB_NAME < migrations/001_init.sql
   mysql -u USER -p -h HOST DB_NAME < migrations/002_phase1a_indexes.sql
   ```
4. For local development:
   ```sh
   php -S localhost:8000 -t public
   ```

## Phase 1A security posture

- magic-link auth only for recruiter users
- hashed one-time tokens with expiry
- generic login responses to avoid email enumeration
- Turnstile on signup and login
- rate limiting backed by MySQL
- CSRF and same-origin checks on state-changing requests
- hardened sessions
- session-based admin login replacing HTTP Basic Auth
- audit logging hooks across auth, admin actions, and chat

## Notes

- In `APP_ENV=dev`, signup/login exposes the magic link onscreen for local testing.
- In `APP_ENV=dev`, Turnstile is bypassed if no secret key is configured.
- Use `ADMIN_SEED_EMAIL`, `ADMIN_SEED_PASSWORD`, and `ADMIN_SEED_NAME` only for local bootstrap of the first admin user.

---
