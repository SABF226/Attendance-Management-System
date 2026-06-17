# 🔄 MERN Redesign & Implementation Plan — BIT English Club Attendance System

> A blueprint for rebuilding the existing PHP/MySQL attendance app as a **greenfield MERN**
> (MongoDB · Express · React · Node.js) project. The current PHP app is the **functional
> specification** — we are not porting code, we are re-implementing the same product on a
> modern JavaScript stack from scratch. This document is written to **onboard a new team**:
> it explains *what* we're building, *why*, and *exactly how* to build it phase by phase.

**Status:** Design / pre-implementation · **Audience:** new engineering team · **Owner:** TBD

---

## Table of contents
1. [Project overview](#1-project-overview)
2. [Domain glossary & business rules](#2-domain-glossary--business-rules)
3. [User roles & journeys](#3-user-roles--journeys)
4. [Feature parity checklist](#4-feature-parity-checklist)
5. [Target architecture](#5-target-architecture)
6. [Technology mapping (PHP → MERN)](#6-technology-mapping-php--mern)
7. [Data model](#7-data-model)
8. [REST API contract](#8-rest-api-contract)
9. [Authentication & security](#9-authentication--security)
10. [Frontend design](#10-frontend-design)
11. [Repository structure](#11-repository-structure)
12. [Full implementation plan (phased)](#12-full-implementation-plan-phased)
13. [Environment, tooling & deployment](#13-environment-tooling--deployment)
14. [Testing strategy](#14-testing-strategy)
15. [Definition of done & acceptance](#15-definition-of-done--acceptance)
16. [Open decisions](#16-open-decisions)

---

## 1. Project overview

### What this product is
The **BIT English Club Attendance System** is an internal web app used by a university
English club to **manage members and track attendance** at club sessions (weekly meetings,
team activities). Admins run sessions and keep records; members check in and compete on a
points leaderboard.

### What it does today (the PHP app)
- Admins maintain a **member directory** (name, field of study, phone, email).
- Admins create **sessions** (date, time, team, name) and **take attendance** in a grid,
  marking each member present / absent / excused with optional notes.
- Members can **self-check-in by scanning a per-session QR code**, earning **+10 XP**.
- A **leaderboard** ranks members by XP; a **dashboard** shows attendance analytics.
- Sessions can be **exported** to PDF / Excel / CSV.
- The app is **role-based**: `admin` (full access) vs `member` (dashboard, leaderboard, QR scan only).

### Why rebuild in MERN
The PHP app mixes routing, controllers, SQL, and HTML in a single `index.php` front
controller (`?page=&action=`). A MERN rebuild gives:
- Clean **API ↔ UI separation** (JSON API + React SPA).
- One language across the stack (TypeScript), shared types and validation.
- A component-based, testable frontend.
- An easy runway for the unfinished roadmap (email notifications, deeper analytics,
  multi-language, PWA, real-time attendance).

### Goals & non-goals
**Goals:** full feature parity, equal-or-better security, a maintainable codebase, clear
seams for the roadmap items.
**Non-goals (for v1):** changing the product's behaviour, building a native mobile app,
multi-tenant support, or migrating historical data unless explicitly requested (greenfield;
optional import script noted later).

---

## 2. Domain glossary & business rules

### Glossary
| Term | Meaning |
|---|---|
| **Member / User** | A club member. Also the auth identity (has role + password). |
| **Admin** | Club officer with full management rights. |
| **Session** | A dated club meeting that attendance is recorded against. |
| **Attendance record** | One member's status for one session (`present`/`absent`/`excused`). |
| **QR check-in** | Member self-marks present by scanning a session's QR token. |
| **XP / Points** | Gamification score; +10 per successful QR check-in. |
| **Field** | Member's field of study (e.g. Computer Science). |

### Business rules (must be preserved exactly)
1. **One record per member per session.** Enforced today by a SQL unique key
   `(session_id, member_id)`; in Mongo this becomes a **compound unique index**.
2. **QR token validity:** a scan succeeds only if the session's QR is **active** *and*
   **not expired** (`qr_code_expires_at > now`).
3. **No duplicate check-in:** if a member already has a record for the session, the scan is
   rejected with "already recorded".
4. **+10 XP** is awarded atomically on a successful check-in (and only then).
5. **Scan rate limit:** max **5 scan attempts per minute per member**.
6. **Role gating:** members may only reach **dashboard, leaderboard, QR scan** (+ logout);
   everything else is admin-only. Unauthenticated users are redirected to the auth page.
7. **Attendance statuses:** exactly `present`, `absent`, `excused` (default `present`).
8. **Unique email** per member.
9. **Seeded accounts (dev):** an admin (`admin123`) and a member (`member123`) — replicate
   as seed scripts, never hard-code in production.
10. **No sensitive data in error responses** (generic messages in production).

---

## 3. User roles & journeys

### Admin journey
1. Log in → lands on **admin dashboard** (totals: members, sessions, overall stats, recent sessions).
2. Manage **members** (add / edit / delete / search, view a member's attendance history).
3. Create a **session**, then **take attendance** in a grid (bulk "mark all present", per-row
   toggle, notes, keyboard shortcuts **P/A/E**), and **save**.
4. Generate a **QR code** for a session and display it; **deactivate** when done.
5. **Export** a session to PDF / Excel / CSV.
6. View **leaderboard** and **analytics charts**.

### Member journey
1. Register / log in → lands on a **member dashboard** (own stats + personal attendance history).
2. Open the **scanner**, scan the session QR → marked present, **+10 XP**, sees new total.
3. Check standing on the **leaderboard**.

---

## 4. Feature parity checklist

Every item below must behave the same for end users in the MERN build.

| Domain | Behaviour to preserve |
|---|---|
| **Auth & roles** | Login / register / logout; `admin` vs `member`; route guarding; redirect guests |
| **Members** | CRUD; search by name/email; per-member attendance history |
| **Sessions** | Create; list with filter (date range, status) + sort; delete; monthly stats |
| **Attendance** | Bulk grid (present/absent/excused + notes); 1 record per member per session; P/A/E shortcuts |
| **QR check-in** | Admin generate/deactivate expiring token; member scan; +10 XP; duplicate-proof; 5/min limit |
| **Gamification** | `points` on members; top-attendees leaderboard with podium |
| **Dashboard** | Admin: totals + recent + overall stats. Member: own stats + history |
| **Exports** | PDF / Excel / CSV per session |
| **Security** | CSRF equivalent; rate limiting; input validation; secure sessions; security headers |

---

## 5. Target architecture

```
┌─────────────────────────────┐         ┌──────────────────────────────┐
│         React SPA           │  HTTPS  │      Node.js + Express API    │
│  (Vite + React Router)      │ ◄─────► │      REST /api/v1/*           │
│  - Auth context (JWT cookie)│  JSON   │  - Controllers / Services    │
│  - React Query data layer   │         │  - Mongoose models           │
│  - QR generate + scan        │         │  - JWT auth + RBAC middleware│
│  - Charts (Recharts)        │         │  - Helmet / rate limit / zod │
└─────────────────────────────┘         └───────────────┬──────────────┘
                                                         │
                                                ┌────────▼─────────┐
                                                │     MongoDB      │
                                                │  (Mongoose ODM)  │
                                                └──────────────────┘
```

Two deployable units: the **API server** (Node/Express) and the **client** (static React
build). In production, the simplest setup serves the built client from the API (single
origin → simpler cookies/CSRF).

---

## 6. Technology mapping (PHP → MERN)

| Concern | Current (PHP) | MERN replacement |
|---|---|---|
| Routing | `index.php` front controller (`?page=&action=`) | Express routers + React Router |
| Controllers | `controllers/*.php` | Express controllers + service layer |
| Models / DB | PDO + raw SQL (`models/*.php`) | Mongoose schemas + models |
| Database | MySQL (relational) | MongoDB (document, references) |
| Views | `views/*.php` server-rendered | React components (Vite) |
| Auth | `$_SESSION` + `password_hash` | JWT in httpOnly cookie + `bcrypt` |
| CSRF | `Security::requireCsrf()` | double-submit CSRF token |
| Rate limiting | `Security::checkRateLimit()` | `express-rate-limit` |
| Security headers | `config/security.php` | `helmet` |
| Validation | `Security::string/int/isValidEmail` | `zod` (shared client+server) |
| QR generate | `qrcode.min.js` + DB token | `qrcode` npm + `crypto` token |
| QR scan | `html5-qrcode.min.js` | `html5-qrcode` / react wrapper |
| Charts | Chart.js | Recharts (or react-chartjs-2) |
| PDF export | FPDF / mPDF | `pdfkit` (or `puppeteer`) |
| Excel export | PhpSpreadsheet | `exceljs` |
| Config | `config/database.php` | `.env` + `dotenv`, validated by zod |
| Tests | PHPUnit | Vitest + Supertest / React Testing Library |

**Language:** TypeScript end-to-end. Share types + zod schemas in `packages/shared`.

---

## 7. Data model

The data is relational (members ↔ sessions via records with a unique pair). Model it in
Mongo with **references, not deep embedding**, because records grow unbounded and are read
from both the member and session sides.

**`users`** (was `members` + auth columns)
```ts
{
  _id: ObjectId,
  name: string,                 // required
  field: string,                // field of study, required
  phone: string,                // required
  email: string,                // required, unique, lowercased
  passwordHash: string | null,  // bcrypt; null until registered
  role: 'admin' | 'member',     // default 'member'
  points: number,               // default 0
  createdAt, updatedAt
}
// Indexes: { email: 1 } unique
```

**`sessions`** (was `attendance_sessions` + QR columns)
```ts
{
  _id: ObjectId,
  name: string,                 // session_name
  date: Date,                   // session_date
  time?: string | null,
  team?: string | null,
  qr: {
    token: string | null,       // unique sparse
    expiresAt: Date | null,
    isActive: boolean           // default false
  },
  createdAt
}
// Indexes: { date: -1 }, { 'qr.token': 1 } unique sparse
```

**`attendanceRecords`** (was `attendance_records`)
```ts
{
  _id: ObjectId,
  session: ObjectId,            // ref 'Session'
  member: ObjectId,             // ref 'User'
  status: 'present' | 'absent' | 'excused', // default 'present'
  notes?: string | null,
  checkInTime?: Date | null,    // set on QR check-in
  createdAt
}
// Indexes: { session: 1, member: 1 } UNIQUE  ← replaces SQL unique key
//          { member: 1 }
```

> **Cascade deletes** were `ON DELETE CASCADE` in SQL. In Mongo this is application-level:
> deleting a session/user must also delete its `attendanceRecords` (do it in a service,
> ideally inside a transaction).

---

## 8. REST API contract

Base: `/api/v1`. List endpoints return `{ data, page, total }`; errors use
`{ error: { code, message } }` (generic in prod).

### Auth
| Method | Path | Role |
|---|---|---|
| POST | `/auth/register` | public |
| POST | `/auth/login` | public |
| POST | `/auth/logout` | auth |
| GET | `/auth/me` | auth |

### Members
| Method | Path | Role |
|---|---|---|
| GET | `/members?search=&page=` | admin |
| POST | `/members` | admin |
| GET | `/members/:id` | admin / self |
| PUT | `/members/:id` | admin |
| DELETE | `/members/:id` | admin |
| GET | `/members/:id/attendance` | admin / self |

### Sessions & attendance
| Method | Path | Role |
|---|---|---|
| GET | `/sessions?from=&to=&status=&sort=` | admin |
| POST | `/sessions` | admin |
| GET | `/sessions/:id` | admin |
| DELETE | `/sessions/:id` | admin |
| GET | `/sessions/:id/attendance` | admin |
| PUT | `/sessions/:id/attendance` | admin (bulk grid save) |
| GET | `/sessions/:id/export?format=pdf\|excel\|csv` | admin |

### QR
| Method | Path | Role |
|---|---|---|
| POST | `/sessions/:id/qr` | admin (generate) |
| DELETE | `/sessions/:id/qr` | admin (deactivate) |
| POST | `/qr/scan` | member |

### Stats
| Method | Path | Role |
|---|---|---|
| GET | `/stats/overview` | admin |
| GET | `/stats/me` | member |
| GET | `/stats/leaderboard?limit=20` | auth |
| GET | `/stats/dashboard` | admin |

---

## 9. Authentication & security

- **Hashing:** `bcrypt` (PHP already uses bcrypt; hashes are portable if data is imported later).
- **Tokens:** JWT in an **httpOnly, Secure, SameSite=Strict cookie** (mirrors current session
  cookie hardening; avoids localStorage XSS exposure).
- **CSRF:** cookie auth ⇒ add a **double-submit CSRF token** for mutating requests.
- **RBAC:** `requireAuth` → `requireRole('admin')` middleware; replaces the inline role gate
  in `index.php`.
- **QR scan exception:** `/qr/scan` is an authenticated member route; the QR **token** itself
  is the anti-forgery material (validated against DB: active + unexpired) plus per-user rate
  limiting — mirroring the PHP scan endpoint that skips global CSRF and verifies its own token.
- **Parity table** (against `SECURITY.md`):

| Current | MERN equivalent |
|---|---|
| CSRF token | double-submit CSRF middleware |
| SQL injection (PDO) | Mongoose params; never build queries from raw input |
| XSS (`htmlspecialchars`) | React auto-escaping; sanitize any `dangerouslySetInnerHTML` |
| Security headers | `helmet` |
| Rate limiting | `express-rate-limit` |
| Session hardening | httpOnly/Secure/SameSite cookie + short JWT TTL + refresh |
| Input validation | `zod` on every endpoint |
| No data leakage | central error handler, generic prod messages |

---

## 10. Frontend design

- **Stack:** React + Vite + TypeScript, React Router, **React Query** (server state),
  Recharts (charts), a QR scan component (`html5-qrcode`), `qrcode` for display.
- **State:** auth in a context (bootstrapped from `GET /auth/me`); everything else via
  React Query hooks — no global store needed for v1.
- **Routing & guards:** `<ProtectedRoute>` (auth) and `<AdminRoute>` (role) wrappers
  replicate the PHP redirect logic.
- **Key pages:** `Auth` (sliding login/register), `Dashboard` (admin vs member variant),
  `Members` (list/search/detail/form), `Sessions` (list/create/take-attendance/view),
  `QRDisplay` (admin), `Scanner` (member), `Leaderboard`.
- **UX parity:** sidebar nav (collapsible), toast notifications, the attendance grid with
  **P/A/E keyboard shortcuts** and "mark all present / reset".

---

## 11. Repository structure

```
attendance-mern/
├── package.json                 # pnpm/npm workspaces
├── packages/
│   └── shared/                  # zod schemas + TS types (client & server)
├── apps/
│   ├── api/
│   │   ├── src/
│   │   │   ├── config/          # env, db connection
│   │   │   ├── models/          # User, Session, AttendanceRecord
│   │   │   ├── modules/
│   │   │   │   ├── auth/        # controller, service, routes
│   │   │   │   ├── members/
│   │   │   │   ├── sessions/
│   │   │   │   ├── attendance/
│   │   │   │   ├── qr/
│   │   │   │   └── stats/
│   │   │   ├── middleware/      # auth, rbac, rateLimit, csrf, errorHandler
│   │   │   ├── lib/             # pdf, excel, qrToken helpers
│   │   │   ├── app.ts
│   │   │   └── server.ts
│   │   ├── scripts/             # seed.ts, (optional) importFromMysql.ts
│   │   └── tests/
│   └── web/
│       ├── src/
│       │   ├── api/             # typed React Query hooks
│       │   ├── auth/            # AuthContext, route guards
│       │   ├── components/      # Sidebar, Toast, Charts, QR, AttendanceGrid
│       │   ├── pages/
│       │   └── main.tsx
│       └── tests/
└── README.md
```

---

## 12. Full implementation plan (phased)

Build **vertically** — each phase delivers one feature end-to-end (DB → API → UI → tests) so
there is always something demoable. Each phase lists **tasks** and **acceptance criteria (AC)**.

### Phase 0 — Project scaffold (foundations)
**Tasks**
- Init monorepo (`pnpm` workspaces); add `apps/api`, `apps/web`, `packages/shared`.
- TypeScript, ESLint, Prettier, `.editorconfig`, Husky pre-commit.
- API: Express app, `helmet`, `cors`, JSON body parsing, central error handler, `/health`.
- DB: Mongoose connection module reading `MONGODB_URI`; fail-fast on boot.
- `.env.example`; env validated with zod at startup.
- Web: Vite React TS app, React Router shell, React Query provider, Vite proxy `/api` → API.
- CI: lint + typecheck + test on PR.

**AC:** `pnpm dev` runs API + web; `/health` returns `{ ok: true }`; CI green on a trivial PR.

---

### Phase 1 — Authentication & RBAC
**Tasks**
- `User` Mongoose model (with `passwordHash`, `role`, `points`).
- `auth` module: `register`, `login`, `logout`, `me`. bcrypt hashing.
- Issue JWT in httpOnly Secure SameSite cookie; `requireAuth` + `requireRole` middleware.
- CSRF double-submit middleware for mutating routes.
- Seed script: admin (`admin123`) + member (`member123`) — **dev only**.
- Web: `AuthContext`, login/register page (sliding UI), `<ProtectedRoute>` / `<AdminRoute>`,
  axios/fetch client that sends the CSRF header and credentials.

**AC:** can register & log in; cookie set; `GET /auth/me` returns the user; members are blocked
from admin routes (403/redirect); logout clears the cookie.

---

### Phase 2 — Members (first full CRUD vertical)
**Tasks**
- `members` module: list (with `search`, pagination), create, get, update, delete.
- zod schemas in `packages/shared` (name, field, phone, email; password optional for admin-created).
- Unique-email handling with a friendly error.
- Web: members list (card on mobile / table on desktop), search box, create/edit form,
  member detail page (shell for attendance history added in Phase 4).

**AC:** admin can CRUD and search members; duplicate email rejected cleanly; non-admins can't access.

---

### Phase 3 — Sessions
**Tasks**
- `Session` model; `sessions` module: create, list (filter `from`/`to`/`status`, `sort`), get, delete.
- Cascade: deleting a session removes its `attendanceRecords` (transaction).
- Monthly stats aggregation (session counts, avg attendance) for the list page.
- Web: sessions list with filters + sort, create form, session detail shell.

**AC:** admin can create/list/filter/sort/delete sessions; deleting a session removes its records;
monthly stats render.

---

### Phase 4 — Attendance grid (core workflow)
**Tasks**
- `AttendanceRecord` model with the **compound unique index** `{ session, member }`.
- `GET /sessions/:id/attendance` returns all members joined with their record (or default absent).
- `PUT /sessions/:id/attendance` bulk upserts the grid (status + notes), idempotent.
- `GET /members/:id/attendance` for the member detail page.
- Web: `AttendanceGrid` with per-row P/A/E controls, **keyboard shortcuts P/A/E**,
  "mark all present" / "reset all", notes field, save.

**AC:** taking attendance saves correctly; re-saving updates (no duplicates); member history shows;
unique index prevents double records under concurrency.

---

### Phase 5 — QR check-in (highest-value new feature)
**Tasks**
- `POST /sessions/:id/qr`: generate `crypto.randomBytes(32).toString('hex')`,
  set `expiresAt = now + QR_TTL_MINUTES`, `isActive = true`; return token/URL.
- `DELETE /sessions/:id/qr`: deactivate.
- `POST /qr/scan` (member, **rate-limited 5/min/user**), inside a transaction:
  1. find session by `qr.token` where active & `expiresAt > now`; else `invalid/expired`;
  2. reject if member already has a record (`already recorded`);
  3. insert record `status: present, checkInTime: now`;
  4. `$inc` member `points` by 10; return `{ newPoints }`.
- Web: admin `QRDisplay` page (renders QR, regenerate/deactivate); member `Scanner` page
  (`html5-qrcode`), success toast with "+10 XP" and new total.

**AC:** valid scan records attendance once and adds 10 XP; expired/inactive token rejected;
second scan rejected; 6th scan within a minute rate-limited.

---

### Phase 6 — Gamification / leaderboard
**Tasks**
- `GET /stats/leaderboard?limit=` → users sorted by `points` desc.
- Web: leaderboard page with **podium (top 3)** + ranked list + member/field search filter.

**AC:** leaderboard reflects points in real time after check-ins; podium renders.

---

### Phase 7 — Dashboard & analytics
**Tasks**
- Aggregation pipelines: overall stats, attendance trend (last N sessions), status
  distribution, per-member stats.
- `GET /stats/overview` (admin) and `GET /stats/me` (member); `GET /stats/dashboard` (chart data).
- Web: admin dashboard (totals, recent sessions, charts) and member dashboard (own stats +
  history), branching on role.

**AC:** charts match the data; admin vs member dashboards differ correctly.

---

### Phase 8 — Exports
**Tasks**
- `GET /sessions/:id/export?format=pdf|excel|csv`.
- PDF via `pdfkit`; Excel via `exceljs`; CSV via stream. Correct `Content-Type` /
  `Content-Disposition`.
- Web: export buttons on the sessions list / detail.

**AC:** each format downloads and opens correctly with the session's attendance.

---

### Phase 9 — Hardening & polish
**Tasks**
- Audit `helmet`, rate limits, CSRF coverage, zod on every endpoint, error envelope.
- Logging (pino) + request IDs; no sensitive data in prod errors.
- Accessibility + responsive pass; toast system; loading/empty/error states.
- Fill out test suites (see §14) and reach the agreed coverage bar.

**AC:** security checklist (§9) fully satisfied; tests green; Lighthouse/a11y acceptable.

---

### Phase 10 — Roadmap extras (post-parity)
- **Email notifications** (session reminders/reports) — `nodemailer` + a queue.
- **Advanced analytics** — attendance trends **by field of study** (the gap in the current app).
- **Multi-language (i18n)** — `react-i18next`.
- **PWA** — installable + offline shell for the scanner.
- **Backup & Restore** — scheduled `mongodump`/`mongorestore` (or **MongoDB Atlas** automated
  backups + point-in-time restore); add an admin-triggered on-demand export and a documented
  restore runbook. Treat as an **ops** concern as much as a feature.
- **(Optional) real-time** — `socket.io` live attendance count as admins watch check-ins.

> **Optional data import:** if real club data must carry over, write `importFromMysql.ts` to
> read `deployment_db_dump.sql` and insert into MongoDB. bcrypt hashes are reusable, so
> existing logins keep working.

### Milestone view
| Milestone | Phases | Outcome |
|---|---|---|
| **M1 — Walking skeleton** | 0–1 | Auth works end-to-end, deployable shell |
| **M2 — Admin core** | 2–4 | Members + sessions + attendance management |
| **M3 — Member value** | 5–7 | QR check-in, leaderboard, dashboards |
| **M4 — Parity complete** | 8–9 | Exports + hardening = feature parity with PHP app |
| **M5 — Beyond parity** | 10 | Roadmap features |

### Roadmap reconciliation (README roadmap → reality → MERN phase)
The PHP `README.md` lists a roadmap; several items are already shipped and some are partial.
This table reconciles that list against actual status and where each lands here.

| README roadmap item | Actual status in PHP app | MERN phase |
|---|---|---|
| **User Authentication** | ✅ Built (roles, login/register) | **Phase 1** (rebuilt as JWT + RBAC) |
| **QR Code Check-In** *(shipped, not on README list)* | ✅ Built | **Phase 5** |
| **Gamification / leaderboard** *(shipped, not on README list)* | ✅ Built | **Phases 5–6** |
| **Email Notifications** | ❌ Not built | **Phase 10** |
| **Advanced Analytics** | 🟡 Partial — per-member yes, **by field of study no** | **Phase 7** (per-member) + **Phase 10** (by field) |
| **Multi-language Support** | ❌ Not built | **Phase 10** |
| **Mobile App / PWA** | ❌ Not built (responsive only) | **Phase 10** |
| **Backup & Restore** | ❌ Not built | **Phase 10** (mongodump/Atlas + restore runbook) |

> Takeaway: "User Authentication" is **already done** and should not read as a pending item;
> the rebuild treats it as foundational (Phase 1). Only Email, by-field analytics, i18n, PWA,
> and Backup/Restore are genuinely net-new.

---

## 13. Environment, tooling & deployment

**Env vars (API `.env`):**
```
MONGODB_URI=
JWT_SECRET=
JWT_TTL=15m
REFRESH_TTL=7d
QR_TTL_MINUTES=10
CLIENT_ORIGIN=http://localhost:5173
COOKIE_DOMAIN=localhost
NODE_ENV=development
```
Validate on boot with zod; never commit real secrets (`.env.example` only).

**Local dev:** MongoDB via Docker Compose; `pnpm dev` runs API + Vite concurrently; Vite
proxies `/api` to Express.

**Build & deploy:** `vite build` → static client; API as a Node process. Options:
- API on Render/Railway/Fly + **MongoDB Atlas**; client on Vercel/Netlify, **or**
- single origin: serve the built client from Express (simplest cookies/CSRF). *Recommended.*

---

## 14. Testing strategy

- **API:** Vitest + Supertest against `mongodb-memory-server`. Prioritise:
  QR transaction (expiry, duplicate, rate limit, points), RBAC guards, attendance upsert,
  cascade deletes, auth flows.
- **Client:** React Testing Library — auth forms, route guards, the attendance grid, scanner UI.
- **E2E (optional):** Playwright — login → take attendance → QR scan happy paths.
- **CI gates:** lint + typecheck + unit tests required on every PR.

---

## 15. Definition of done & acceptance

A phase is "done" when: its AC pass, tests are written and green, it's code-reviewed, and the
demo path works in a deployed preview. **Overall v1 is done** when the entire
[feature parity checklist](#4-feature-parity-checklist) and the
[security parity table](#9-authentication--security) are satisfied, and all 10 business rules
in §2 hold under test.

---

## 16. Open decisions

1. **TypeScript vs JS** — recommend TypeScript (shared types/validation pay off fast).
2. **MongoDB vs relational (PERN/Postgres)** — data is relational but models fine in Mongo with
   references; if strict relational integrity is paramount, Postgres+Prisma is the honest
   alternative. Recommend Mongo per the brief.
3. **Single origin vs split hosting** — affects cookie/CSRF setup; recommend single origin.
4. **QR token TTL** — confirm the intended minutes; whether codes are reusable until expiry or one-time.
5. **i18n timing** — scaffold `react-i18next` early if multi-language is near-term.
6. **Real-time attendance** — include socket.io live count in v1, or defer to Phase 10.

---

*This document is the blueprint; no application code has been written yet. Confirm the
open decisions in §16 and the team can start at **Phase 0**.*
