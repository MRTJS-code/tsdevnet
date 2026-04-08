# Homepage Content Model

The homepage now follows a three-part composition model:
- fixed hero payload
- ordered `homepage_modules` for the middle of the page
- fixed footer/contact/CV payload

## Why the earlier model was not enough

The earlier Phase 1C/1D direction improved structure, but homepage rendering still depended on named section assembly and wrapper blocks. That made the admin harder to use as more homepage content types were added.

## Canonical homepage structure

### Fixed regions
- `site_settings` continues to store the fixed hero/footer singleton fields.
- `documents` continues to store headshot/CV assets and other public document links.

### Ordered middle modules
- `homepage_modules` stores module type, ordering, activation, and shared display metadata.
- `module_rich_text_sections` stores inline payloads for rich text and CTA/info modules.
- Structured data remains in typed payload tables:
  - `profile_experience`
  - `profile_experience_highlights`
  - `profile_certifications`
  - `profile_technology_groups`
  - `profile_technologies`
  - `portfolio_items`
  - `testimonials`

## What stays legacy

`content_blocks` and `content_items` remain in the repo as older generic CMS tables, but they are no longer the canonical homepage driver.

## Forkability

The repository ships with neutral template data via `scripts/seed_reusable_content.php`. Personal profile details, real headshots, and real CV files should be loaded through a local-only seed path such as `scripts/local/seed_tony_profile.php`, not committed into the reusable template layer.

## Seed split

- `scripts/seed_phase1b.php` handles Phase 1B bootstrap data such as admin users and generic assistant knowledge.
- `scripts/reset_content.php` clears only homepage/profile/CMS content tables.
- `scripts/seed_reusable_content.php` seeds reusable example profile content for forks.
- `scripts/local/reset_tony_content.php.example` and `scripts/local/seed_tony_profile.php.example` show how to keep Tony-specific live content in local ignored files.

## Next phase boundary

This refactor does not build the chatbot, access workflow changes, calendar integration, or free/busy features. It leaves the content model, admin flow, and seed discipline in a cleaner state so those capabilities can be added later without redesigning the homepage again.
