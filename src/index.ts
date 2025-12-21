import express from "express";
import path from "path";
import session from "express-session";
import connectPgSimple from "connect-pg-simple";
import helmet from "helmet";
import cookieParser from "cookie-parser";
import { doubleCsrf } from "csrf-csrf";
import { config } from "./config";
import { loadCurrentUser } from "./middleware/auth";
import { flashMiddleware } from "./middleware/flash";
import publicRoutes from "./routes/public";
import appRoutes from "./routes/app";
import apiRoutes from "./routes/api";
import adminRoutes from "./routes/admin";

const PgStore = connectPgSimple(session);

const app = express();
app.set("trust proxy", 1);
app.set("view engine", "ejs");
app.set("views", path.join(__dirname, "..", "views"));

const {
  doubleCsrfProtection,
  generateToken,
  invalidCsrfTokenError
} = doubleCsrf({
  getSecret: () => config.sessionSecret,
  cookieName: "_csrf",
  cookieOptions: {
    httpOnly: true,
    sameSite: "lax",
    secure: config.sessionSecure
  },
  size: 32,
  ignoredMethods: ["GET", "HEAD", "OPTIONS"],
  getTokenFromRequest: (req) =>
    (req.body && (req.body._csrf || req.body.csrfToken)) ||
    req.headers["csrf-token"]?.toString() ||
    req.headers["x-csrf-token"]?.toString() ||
    (req.query._csrf as string) ||
    ""
});

app.use(
  helmet({
    contentSecurityPolicy: false
  })
);
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(express.static(path.join(__dirname, "..", "public")));
app.use(cookieParser(config.sessionSecret));

const sessionMiddleware = session({
  store: new PgStore({
    conString: config.databaseUrl,
    createTableIfMissing: true
  }),
  secret: config.sessionSecret,
  resave: false,
  saveUninitialized: false,
  cookie: {
    httpOnly: true,
    sameSite: "lax",
    secure: config.sessionSecure,
    maxAge: 7 * 24 * 60 * 60 * 1000
  }
});

app.use(sessionMiddleware as any);

app.use((req, res, next) => (doubleCsrfProtection as any)(req, res, next));
app.use((req, res, next) => loadCurrentUser(req, res, next));
app.use((req, res, next) => flashMiddleware(req, res, next));

app.use((req, res, next) => {
  res.locals.appEnv = config.env;
  res.locals.turnstileSiteKey = config.turnstile.siteKey;
  res.locals.csrfToken = generateToken(req, res);
  next();
});

app.use("/", publicRoutes);
app.use("/app", appRoutes);
app.use("/api", apiRoutes);
app.use("/admin", adminRoutes);

app.use((req, res) => {
  res.status(404).render("error", {
    title: "Not found",
    message: "Page not found",
    csrfToken: res.locals.csrfToken || generateToken(req, res)
  });
});

app.use((err: any, req: express.Request, res: express.Response, _next: express.NextFunction) => {
  if (err?.name === "DoubleCsrfError" || err === invalidCsrfTokenError) {
    return res.status(403).render("error", {
      title: "Security check failed",
      message: "Your session expired. Please refresh and try again.",
      csrfToken: generateToken(req, res)
    });
  }
  console.error(err);
  res.status(500).render("error", {
    title: "Server error",
    message: "Something went wrong.",
    csrfToken: generateToken(req, res)
  });
});

app.listen(config.port, () => {
  console.log(`Server listening on http://localhost:${config.port}`);
});
