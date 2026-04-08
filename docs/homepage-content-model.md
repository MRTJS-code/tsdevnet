# Homepage Content Model

Phase 1C moves the homepage beyond the generic `content_blocks` and `content_items` structure introduced in Phase 1B.

## Why the generic model was not enough

The revised homepage has strongly structured sections that need:

- ordered experience, portfolio, and testimonial rows
- grouped technology lists with stable category keys
- explicit CTA state and hero settings
- uploaded headshot and CV support
- footer links and contact fields with predictable meaning

Trying to force those requirements into generic blocks plus `meta_json` would make the content layer harder to reason about, harder to maintain in admin, and easier to misconfigure.

## What is typed now

The homepage now uses dedicated tables for:

- `site_settings`
- `homepage_experience_entries`
- `homepage_certifications`
- `homepage_technology_groups`
- `homepage_technology_entries`
- `homepage_portfolio_items`
- `homepage_testimonials`
- `homepage_documents`

`SiteContentService::homepage()` now returns named typed sections instead of the older generic block map.

## What stays generic

`content_blocks` and `content_items` are still available for lightweight flexible content where a dedicated table would be unnecessary. In Phase 1C they are intended for optional text-led sections such as:

- `homepage_intro`
- `chatbot_teaser`

## Forkability

The repository still ships with neutral placeholder content. Personal profile details, real headshots, and real CV files should be entered through the admin UI after deployment rather than committed into the template layer.

## Next phase boundary

Phase 1C does not build the chatbot, access workflow changes, calendar integration, or free/busy features. It only leaves CTA and teaser structures ready so those capabilities can be added later without redesigning the homepage again.
