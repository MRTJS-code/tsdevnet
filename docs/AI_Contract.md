# AI Contract

## Purpose
This repository supports a forkable personal executive website with a recruiter-facing portal, CMS-managed content, and a future bounded assistant experience.

The repo must remain:
- reusable by others
- safe for public GitHub hosting
- maintainable on low-cost PHP/MySQL hosting
- structured so future AI-assisted development does not create architecture drift or accidental data leakage

## Working principles
1. Keep the repository forkable.
2. Do not commit secrets, credentials, personal-only files, or sensitive operational data.
3. Prefer maintainable server-rendered PHP over unnecessary frontend complexity.
4. Keep architecture layered and explicit.
5. Use migrations for schema changes.
6. Keep public reusable seed data separate from local personal seed data.
7. Keep AI features bounded, explainable, and safe.
8. Update project docs when changes materially affect architecture, decisions, testing, debugging, or progress tracking.

## Data and security rules
- No secrets in source code, commits, fixtures, or examples.
- No live credentials in `.env.example`, SQL files, PHP seed files, or docs.
- Local Tony-specific content, seed scripts, CV files, and private assets must stay outside tracked git files unless intentionally made public.
- `.gitignore` must protect local-only files, temporary uploads, and private local notes.

## Architecture rules
- Views render presentation only.
- Repositories handle data access.
- Services coordinate application logic and homepage assembly.
- Schema changes must be made through migrations.
- Seed logic must be explicit, repeatable, and environment-appropriate.
- Fixed homepage sections should remain fixed only where design requires it.
- Flexible middle-page content should be handled via ordered homepage modules, not repeated hardcoded homepage sections.

## CMS and content rules
- Hero and footer are fixed design regions.
- Middle-page content is modular, ordered, activatable, and CMS-managed.
- New content types should extend the module model deliberately, not through ad hoc hardcoding.
- Public reusable test data should stay generic.
- Personal live content should be loaded through local-only seed paths or admin UI.

## AI feature boundaries
- Rule-based/profile-safe assistant logic comes before LLM-based features.
- AI responses must be grounded in curated profile content.
- AI must not invent credentials, experience, claims, references, or availability.
- JD comparison and richer AI workflows should only be added after source content, guardrails, and tests are stable.
- Prompt or retrieval logic must not bypass content governance or public/private data boundaries.

## Change expectations for AI/Codex work
Whenever AI-assisted changes are made:
- review related migrations, repositories, services, views, and scripts
- update `docs/Decisions.md` if a design choice changed
- update `docs/Architecture.md` if boundaries changed
- update `docs/Testing_Plan.md` if coverage expectations changed
- update `docs/Debug.md` if a new issue was discovered or resolved
- update `docs/To_Do.md` if roadmap or progress changed

## Quality bar
Changes should be:
- understandable by a human maintainer
- minimal but complete
- testable
- documented
- safe for a public repo

## Non-goals
- Do not turn the site into an overengineered CMS platform.
- Do not add AI features just because the seam exists.
- Do not store personal private data in tracked example content.
