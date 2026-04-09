# Architecture

## Purpose
This document defines the intended structure and boundaries for `tsdevnet`.

## Core principles
- Public GitHub repo
- Forkable by others
- Personal-site starter plus real personal website
- Server-rendered PHP application
- MySQL-backed content, auth, and workflow data
- Strong separation between reusable/public content and local/private content

## Repository boundaries

### Must not be committed
- secrets
- API keys
- real production credentials
- local `.env`
- private personal-only seed scripts
- private content files not intended for public release
- experimental dumps, exports, or logs

### `.gitignore` expectations
`.gitignore` should cover at least:
- `.env`
- local override config files
- local-only seed/reset scripts
- temporary uploads or generated outputs
- local debug artefacts
- personal CV/reference assets not intended for the public starter

## Application layering

### Views
Views should:
- render presentation
- contain minimal logic
- not directly query the database
- not contain business/content orchestration logic

### Repositories
Repositories should:
- handle DB access
- encapsulate query logic
- return clean records/collections
- avoid presentation logic

### Services
Services should:
- assemble content for views
- enforce application rules
- coordinate repositories
- keep homepage composition logic out of templates

### Migrations
Migrations should:
- create/change schema explicitly
- be ordered and repeatable
- not contain environment secrets
- reflect deliberate schema evolution

### Seed/reset scripts
Seed/reset scripts should:
- clearly distinguish public reusable data from local personal data
- be safe to rerun
- be named clearly
- not silently destroy unrelated tables

## Content model direction

### Fixed regions
The homepage has two fixed design regions:
- Hero
- Footer / contact / CV area

These are managed separately from the flexible body content.

### Modular middle content
The homepage body between hero and footer is composed from ordered homepage modules.

Expected behaviour:
- modules can be added
- modules can be edited
- modules can be hidden/shown
- modules can be reordered
- modules can support defined content types

Supported module types:
- rich text
- timeline
- pill cards
- case studies
- list
- quote cards
- CTA banner
- media + text

These names describe render shape only. Editors decide whether a block represents strengths, certifications, portfolio, testimonials, or any other meaning.

### Database design guidance
Prefer explicit structure over excessive JSON.
Use JSON only where it adds flexibility without hiding core business meaning.

Rule of thumb:
- use tables for core entities and repeatable ordered records
- use the homepage module registry for order and shared display metadata
- use JSON only for small presentation metadata where a dedicated column would not add clarity

## Public vs local content

### Public reusable content
Committed seed data should:
- be generic
- create a working example site
- avoid real personal/private content
- support forks

### Local Tony content
Tony-specific live/example content should:
- live in local-only scripts or admin-managed content
- stay outside tracked git files unless intentionally public
- be documented clearly for local setup

## AI/chatbot boundaries
- Rule-based/profile-safe assistant first
- Curated content source before AI generation
- No ungoverned freeform AI behaviour
- No sensitive or private data exposure
- Retrieval/assistant logic must respect content boundaries

## File structure guidance
- `public/` entry points
- `views/` templates and module partials
- `src/Repositories/` DB access
- `src/Services/` application/content assembly logic
- `migrations/` schema changes
- `scripts/` committed setup/reset/seed scripts
- `scripts/local/` local-only private scripts and `.example` templates
- `docs/` architecture/decision/debug/testing/todo artefacts
- `tests/` unit/integration coverage as introduced

## Architecture drift rule
If implementation materially changes the intended structure:
- update this document
- update decisions/testing/todo as needed
- do not let temporary structure become permanent by accident
