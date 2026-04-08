# Homepage Content Model

Phase 1D establishes the canonical profile content model layered on top of the transitional Phase 1C homepage schema. The repo still keeps `content_blocks` and `content_items` from Phase 1B, but the homepage and profile CMS now rely on typed profile tables instead of generic block assembly.

## Why the generic model was not enough

The revised homepage has strongly structured sections that need:

- ordered experience, portfolio, and testimonial rows
- grouped technology lists with stable category keys
- explicit CTA state and hero settings
- uploaded headshot and CV support
- footer links and contact fields with predictable meaning

Trying to force those requirements into generic blocks plus `meta_json` would make the content layer harder to reason about, harder to maintain in admin, and easier to misconfigure.

## What is typed now

The canonical profile homepage now uses dedicated tables for:

- `site_settings`
- `profile_experience`
- `profile_experience_highlights`
- `profile_certifications`
- `profile_technology_groups`
- `profile_technologies`
- `portfolio_items`
- `testimonials`
- `documents`

`SiteContentService::homepage()` still returns named homepage sections, but it now reads from the canonical Phase 1D profile tables rather than the transitional `homepage_*` table set from Phase 1C.

## What stays generic

`content_blocks` and `content_items` are still available for lightweight flexible content where a dedicated table would be unnecessary. In the current profile model they are intended for optional text-led sections such as:

- `homepage_intro`
- `chatbot_teaser`

## Forkability

The repository ships with neutral template data via `scripts/seed_profile_template.php`. Personal profile details, real headshots, and real CV files should be loaded through a local-only seed path such as `scripts/local/seed_tony_profile.php`, not committed into the reusable template layer.

## Seed Split

- `scripts/seed_phase1b.php` is now limited to Phase 1B bootstrap data such as admin users and generic assistant knowledge.
- `scripts/seed_profile_template.php` seeds reusable example profile content for forks.
- `scripts/local/seed_tony_profile.php.example` shows how to keep Tony-specific live content in a local, ignored seed file.

## Next phase boundary

Phase 1D does not build the chatbot, access workflow changes, calendar integration, or free/busy features. It only leaves CTA, teaser, and profile structures ready so those capabilities can be added later without redesigning the homepage again.
