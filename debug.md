## Debug log

- Fix attempt: Resolved Express middleware type error (`PathParams` overload) by wrapping CSRF/auth/flash middlewares in `(req, res, next) => ...` functions in `src/index.ts`. If this does not resolve the issue in Render, do **not** re-apply this change; instead revisit middleware typing or Express typings alignment. (Date: current session)
- Fix attempt: Cast session middleware `session(...)` to `any` when passing to `app.use` in `src/index.ts` to work around Express typing mismatch. If this does not resolve the issue, do not re-apply; investigate express-session / @types versions instead. (Date: current session)
- Fix attempt: Add `userId` relation on `AuditLog` to satisfy Prisma relation warning; schema updated and new migration `002_auditlog_user_rel` added. If this causes issues, re-evaluate relationships instead of reapplying. (Date: current session)
