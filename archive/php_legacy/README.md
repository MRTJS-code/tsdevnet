# Tony Smith Recruiter Portal (PHP 8 + MySQL 8)

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
   - Admin basic auth credentials
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

## Turnstile
- Create a Turnstile site + secret key at Cloudflare.
- Add keys to `config.php` under `turnstile`.
- Widgets are rendered on signup/login via the JS snippet; server-side verification enforces pass/fail.

## SMTP
- Default points to `localhost:25` with no auth.
- Set `smtp_username`/`smtp_password` and `smtp_secure` (`tls`/`ssl`) if required.
- Sender defaults to `noreply@example.com` and name "Tony Smith".

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
  - pending → Demo Access (5 msgs/day)
  - approved → Full Access (50 msgs/day)
- `/app/chat.php` enforces CSRF, origin, JSON content-type, and per-user daily limits.
- Messages + conversations are stored; replies are canned in `ChatService::generateReply()`. Swap this out to call Azure OpenAI later (keep limits/logging intact).

## Admin
- HTTP Basic Auth using `ADMIN_USER`/`ADMIN_PASS` from `config.php`.
- `/admin/index.php` lists pending users with Approve/Reject/Block.
- `/admin/user.php?id=...` shows details, notes, conversations/messages; lets you edit admin notes.

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
- Serve `public/` via PHP’s built-in server for quick checks:
  ```sh
  php -S localhost:8000 -t public
  ```
- Ensure MySQL + Turnstile keys are set; in `dev` you can view magic links directly after signup/login.
