# Testing Plan

## Purpose
This document defines the current testing expectations for `tsdevnet`.

The project is still in an early structured-build phase, so testing is focused on:
- migration correctness
- seed/reset safety
- repository/service behaviour
- homepage rendering contracts
- CMS/admin structural behaviour
- regression prevention for core design rules

## Current testing scope

### 1. Database migration checks
Each migration should be checked to confirm:
- it runs cleanly on an empty database
- it runs in the intended order
- tables, indexes, constraints, and foreign keys are created as expected
- schema changes support the fixed hero, ordered modules, and fixed footer model
- data migrations preserve visible homepage content where the migration depends on existing tables

### 2. Seed/reset script checks
Seed and reset scripts should be checked to confirm:
- committed reusable test content loads successfully
- `scripts/reset_content.php` wipes only intended content tables
- auth/system tables are not accidentally destroyed
- rerunning reset + seed leaves a working homepage
- local Tony-only seed paths are excluded from git and documented properly

### 3. Repository tests
Repository-level tests should verify:
- create/read/update/list operations behave as expected
- homepage module sort order is respected
- active/inactive filtering works
- fixed content regions load correctly
- rich-text module payloads load correctly
- typed content sources remain queryable independently of module ordering

### 4. Service tests
Service-level tests should verify:
- homepage content assembly works with fixed hero + ordered modules + fixed footer
- unsupported or empty module payloads fail safely
- document/social/contact resolution behaves correctly
- portfolio featured filtering behaves correctly
- assistant rule matching stays within expected safe behaviour

### 5. Homepage render/design contract checks
The homepage should be checked for:
- hero always rendering first
- footer/contact/CV area always rendering last
- middle modules rendering in CMS-defined order
- hidden/inactive modules not rendering
- empty states failing gracefully
- no missing payload causing template crashes
- timeline detail interaction working without modal fragility

### 6. Admin/CMS checks
At minimum, structural/admin checks should verify:
- admin can create homepage modules
- admin can edit homepage modules
- admin can reorder homepage modules
- admin can activate/deactivate homepage modules
- hero/footer remain managed separately
- invalid module type handling is safe

## Regression checklist
Run this checklist whenever homepage/CMS changes are made:
- hero still fixed
- footer still fixed
- middle blocks still orderable
- reusable seed still produces a working public homepage
- local-only content still stays outside tracked files
- CV link still works
- social/contact fields still load
- no personal/local-only content leaked into tracked seed data

## Error capture expectations
Whenever a bug or failed test is found:
- record it in `docs/Debug.md`
- capture the symptom
- note the root cause if known
- note the fix
- add or update a test where practical

## Test locations
Suggested structure:
- `tests/Unit/Repositories/`
- `tests/Unit/Services/`
- `tests/Integration/Database/`
- `tests/Integration/Homepage/`

Exact tooling can evolve, but tests should stay aligned with repository/service boundaries.

Current repo command:
- `php tests/run.php`
- `php tests/run.php --mysql-migrations`

MySQL migration integration notes:
- the migration integration test uses the configured MySQL connection from `config/app.php` and local env/config overrides
- it creates a temporary database, runs all migrations in order, verifies key schema objects, and drops the temporary database after the test
- this is the rollback mechanism for migration testing because MySQL DDL is not reliably transaction-safe across the full migration set

## Test data rules
- Reusable committed test data must remain generic.
- Local Tony-only data must not be required for public CI/dev tests.
- Tests must not depend on private files or local-only scripts.
- Fixtures should be minimal and purposeful.

## Browser automation
Playwright browser coverage is now part of the homepage regression baseline:
- homepage loads successfully
- hero renders
- footer renders
- modules render in seeded order
- inactive module does not render
- timeline details expand/collapse
- CTA/banner links render when seeded
- footer CV / LinkedIn / GitHub links render when seeded

## Not yet covered
The following are future areas, not current mandatory coverage:
- visual regression testing
- accessibility automation
- chatbot conversation quality testing
- LLM prompt/retrieval testing
- calendar/free-busy integration tests

## Minimum expectation for each change
For any meaningful change, confirm:
1. migration/setup impact
2. seed/reset impact
3. repository/service behaviour
4. homepage regression impact
5. documentation impact
