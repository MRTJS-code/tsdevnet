# Debug Log

This file tracks issues, causes, fixes, and prevention actions.

> Note: historical bugs are only included here where they are clearly observable from current implementation or current project direction. This is not a reconstructed full bug history.

---

## Current observed design issue
### Date
Undated / current observed issue

### Area
CMS / homepage content model

### Symptom
Homepage content management became too rigid because sections were explicitly coded and split across many dedicated admin screens.

### Root cause
The homepage evolved around named hardcoded sections rather than a modular ordered body-content model.

### Fix
Introduce fixed hero and footer regions with an ordered `homepage_modules` registry for the middle-page content.

### Prevention
Avoid adding new homepage sections as one-off hardcoded template regions unless they are truly fixed by design.

### Related tests
- homepage render contract
- ordered content module tests
- admin reorder tests

---

## Current observed content-seeding issue
### Date
Undated / current observed issue

### Area
Seed content / local personal content

### Symptom
Generic public seed data and Tony-specific live content were at risk of being mixed together, which confuses setup and weakens privacy/forkability boundaries.

### Root cause
The project needed two distinct seed paths:
- reusable public test/demo content
- local-only Tony content

### Fix
Keep committed generic reset/seed scripts separate from local-only Tony reset/seed example scripts.

### Prevention
Protect local scripts with `.gitignore` and document the expected workflow.

### Related tests
- seed/reset isolation tests
- no-private-data-in-public-seeds checks

---

## Current observed setup issue
### Date
2026-04-08

### Area
Setup / migrations / homepage rendering

### Symptom
Typed homepage data alone no longer guarantees a working middle-page render once homepage assembly depends on an ordered module registry.

### Root cause
The current repo maturity required explicit module seed data in addition to the typed content tables.

### Fix
Add `homepage_modules`, `module_rich_text_sections`, and explicit reusable/local seed flows that populate both module ordering and typed payloads.

### Prevention
Treat module-order data as canonical setup state and include it in reset/seed verification.

### Related tests
- migration checks for `007_homepage_modules.sql`
- reusable reset + seed smoke checks

---

## Template for future entries

## [Issue title]
### Date
YYYY-MM-DD

### Area
Example: CMS / homepage / migrations / admin / assistant / tests

### Symptom
What went wrong?

### Root cause
Why did it happen?

### Fix
What changed?

### Prevention
How do we stop it happening again?

### Related tests
Which tests were added or should be added?
