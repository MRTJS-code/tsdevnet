# Tony Smith Recruiter Portal (Node.js + Postgres)

TypeScript Express app with passwordless magic-link auth, Cloudflare Turnstile, admin approvals, a chat UI (demo/full tiers), audit logging, and Postgres persistence. PHP legacy code is preserved in `archive/php_legacy`.

## What's inside
- Express + TypeScript, EJS views, vanilla JS frontend.
- Magic-link auth (15 min, single-use), Turnstile on signup/login, secure sessions via Postgres store.
- Tiered chat limits (demo=5/day, full=50/day), conversation/message logging, simple rule-based ChatService stub.
- Admin console with approval/block/reject, notes, audit log entries, and admin chat replies.
- Notifications via SMTP + optional Telegram; dev mailbox console fallback.
- Prisma schema + SQL migrations, Render deployment manifest.

## Repository layout
- `src/` - server code (routes, services, middleware, config).
- `views/` - EJS templates.
- `public/` - static assets (CSS/JS).
- `prisma/` - Prisma schema and migrations (000_init, 001_live_chat).
- `render.yaml` - Render service + database definition.
- `archive/php_legacy/` - previous PHP implementation, untouched.

## Prerequisites
- Node.js 20+
- Docker (for local Postgres) or a Postgres 14+ instance

## Quickstart (local)
1) Install deps
```sh
npm install
```
2) Start Postgres (example)
```sh
docker run --name tsdevnet-postgres -p 5432:5432 -e POSTGRES_PASSWORD=postgres -e POSTGRES_USER=postgres -e POSTGRES_DB=tsdevnet -d postgres:15
```
3) Configure env
```sh
cp .env.example .env
# fill DATABASE_URL, TURNSTILE_* keys, SMTP, OWNER_EMAIL, ADMIN_PASSWORD, SESSION_SECRET, PUBLIC_BASE_URL
```
4) Generate client + run migrations
```sh
npm run prisma:generate
npm run migrate:dev   # creates tables defined in prisma/migrations/
```
5) Run the app
```sh
npm run dev
# visit http://localhost:3000
```

### Environment variables
- `APP_ENV` (dev/prod) - enables dev mailbox + logging when `dev`.
- `PORT`, `APP_URL` - external URL used for magic links.
- `SESSION_SECRET`, `SESSION_SECURE` - cookie signing + secure flag (true in prod).
- `DATABASE_URL` - Postgres connection string.
- `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY` - Cloudflare Turnstile keys.
- `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`, `SMTP_FROM_EMAIL`, `SMTP_FROM_NAME` - SMTP sender.
- `OWNER_EMAIL` - who receives signup/handoff alerts.
- `ADMIN_PASSWORD` - password for `/admin/login`.
- `DEV_MAILBOX` - set to `console` in dev to log magic links instead of sending.
- `PUBLIC_BASE_URL` - external URL for links in notifications (e.g., `https://yourapp.com`).
- `TELEGRAM_BOT_TOKEN`, `OWNER_TELEGRAM_CHAT_ID` - optional Telegram notifications to owner.

### Auth + security
- Passwordless only. Tokens are SHA-256 hashed, 15 minute TTL, single-use.
- Turnstile verified server-side on signup/login.
- Sessions stored in Postgres via `connect-pg-simple`; secure, HttpOnly, SameSite=Lax cookies.
- CSRF protection via `csurf` on chat/admin/actions; helmet headers enabled.
- Rate limits: IP-based on auth endpoints; per-tier daily message caps on chat; polling endpoints are rate-limited.
- No email enumeration on login; responses are generic.

### Admin usage
- Visit `/admin/login`, enter `ADMIN_PASSWORD`.
- Review pending users, approve/reject/block, add notes, and view conversations.
- Admin chat view (`/admin/chat/:conversationId`) shows threads and allows replies/ack/close. All admin actions are CSRF-protected and logged to `audit_log`.

### Notifications
- `NotificationService` emails `OWNER_EMAIL` on new signups and handoff requests using Nodemailer SMTP config.
- In `APP_ENV=dev` with `DEV_MAILBOX=console`, emails log to console and magic links also show on the confirmation page.
- Telegram (optional): on new signup and live chat/meeting handoff, sends a message to `OWNER_TELEGRAM_CHAT_ID` with admin links.

### Deploy to Render
- Use `render.yaml` (includes web service + Postgres). Render will inject `DATABASE_URL` from the managed DB.
- Build command: `npm install && npm run build && npm run prisma:generate`
- Start command: `npm run start`
- Set env vars in the Render dashboard for Turnstile, SMTP, OWNER_EMAIL, ADMIN_PASSWORD, SESSION_SECRET, APP_URL, PUBLIC_BASE_URL, Telegram keys, etc.
- Ensure `SESSION_SECURE=true` and `APP_ENV=prod` in production.

#### Step-by-step (Render)
1) Push this repo to GitHub/GitLab.
2) In Render, create a Postgres database (Starter). Render will surface `DATABASE_URL` and attach it later.
3) Create a new Web Service from your repo; Render will auto-detect `render.yaml`.
4) In Web Service env vars, set:
   - `APP_ENV=prod`
   - `APP_URL` and `PUBLIC_BASE_URL` to your Render URL (e.g., `https://your-app.onrender.com`)
   - `SESSION_SECRET` (generate a strong value)
   - `SESSION_SECURE=true`
   - `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY`
   - `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`, `SMTP_FROM_EMAIL`, `SMTP_FROM_NAME`
   - `OWNER_EMAIL`, `ADMIN_PASSWORD`
   - Optional: `TELEGRAM_BOT_TOKEN`, `OWNER_TELEGRAM_CHAT_ID`
5) Link the Postgres DB under “Linked Resources” so `DATABASE_URL` is auto-set.
6) Deploy. Render runs `npm install && npm run build && npm run prisma:generate` then `npm run start`.
7) Run migrations once per environment: open a Render shell and run `npm run migrate:deploy`.
8) Smoke test:
   - Visit `/` and `/signup` (in dev with `DEV_MAILBOX=console`, magic link shows on page).
   - Admin: `/admin/login` with `ADMIN_PASSWORD`, approve a user, open `/admin/chat/:conversationId`, send a reply, confirm recruiter sees it.

### Database schema
Prisma schema covers: users, access_tokens, conversations (status/last_activity), messages (metadata/is_owner_reply), rate_limits, audit_log, handoff_requests (linked to conversation), and session store. SQL is in `prisma/migrations/000_init/migration.sql` and `prisma/migrations/001_live_chat/migration.sql`.

### Adding Azure OpenAI / Foundry later
- Implement `ChatService.generateLLMReply` (or swap body of `generateReply`) to call your LLM, keeping inputs as the message history and tier.
- Preserve rate limiting, logging, and session handling already in `api/chat`.
- Inject credentials via env vars; do not expose keys client-side.
- Consider capturing token usage + model name in `audit_log.metadata` when you wire it up.

### Telegram setup
1. Create a bot via BotFather (`/newbot`) and copy the bot token.
2. Get your chat_id: send a message to the bot, then call `https://api.telegram.org/bot<token>/getUpdates` from your browser/curl and read your `chat.id`.
3. Set `TELEGRAM_BOT_TOKEN` and `OWNER_TELEGRAM_CHAT_ID` in your Render env. Also set `PUBLIC_BASE_URL` so admin links in notifications are correct.

### Live chat (polling)
- Both recruiter and admin chat views poll `/api/conversations/:id/messages?after=<timestamp>` every ~2-3 seconds with simple backoff on errors.
- Admin replies are stored as `is_owner_reply=true` and rendered in the recruiter UI.
- Handoff "Request a chat with Tony" creates a handoff request tied to the conversation; Tony/owner is alerted via Telegram/email. Conversation status can be acknowledged/closed in the admin view.

### NPM scripts
- `npm run dev` - ts-node-dev watcher
- `npm run build` - TypeScript build to `dist/`
- `npm start` - run compiled server
- `npm run migrate:dev` / `npm run migrate:deploy` - Prisma migrations
- `npm run prisma:generate` - regenerate Prisma client

### Legacy PHP
- Original PHP/MySQL implementation is preserved in `archive/php_legacy/` for reference and is no longer used for deployment.
