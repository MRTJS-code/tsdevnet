## Debug log

- Fix attempt: Resolved Express middleware type error (`PathParams` overload) by wrapping CSRF/auth/flash middlewares in `(req, res, next) => ...` functions in `src/index.ts`. If this does not resolve the issue in Render, do **not** re-apply this change; instead revisit middleware typing or Express typings alignment. (Date: current session)
