# Decisions

This file records design and architecture choices made during development.

---

## Undated / inferred from implementation

### Public forkable repository
The project is intentionally public and should remain reusable by others as a personal-site starter.

### Server-rendered PHP approach
The site uses a lightweight server-rendered PHP structure suitable for traditional low-cost hosting.

### MySQL-backed application structure
Core site data, portal flows, and CMS content are intended to be database-backed.

### Recruiter portal as differentiator
The recruiter-facing portal and future bounded assistant capability are part of the product direction, but gated and bounded.

---

## 2026-04-08

### Separate public reusable content from Tony-specific content
Personal content should not be forced into the public reusable seed path.
Generic content belongs in committed seeds.
Tony-specific content belongs in local-only seed/setup paths or in admin-managed live content.

### Fixed hero and footer with modular middle-page content
The homepage should not rely on rigid hardcoded named sections.
Hero and footer remain fixed by design.
Middle-page content is now driven by ordered homepage modules.

### Registry plus typed payloads
Homepage ordering and activation are handled by a module registry, while structured payloads continue to live in explicit tables for experience, certifications, technology groups, portfolio, testimonials, and rich text sections.

### Broad positioning
The site should remain broad enough to support multiple senior role families rather than narrowly optimising for one job title.

### Title choice
Use:
**Enterprise Systems, Data & Integration Leader**

### Homepage CTA direction
Initial CTA is:
**Register & request to chat**

### Technology presentation
Technology should be grouped as:
- core strengths
- supporting tools / platforms
- exposure / familiarity

### Testimonial approach
Homepage testimonials should be short quote cards, not full reference letters.

### CV handling
PDF CV should be downloadable from the bottom of the homepage with contact details.

### AI sequencing
AI/chatbot capabilities should not lead the build.
Curated content structure, CMS control, and safe rule-based behaviour come first.

### Chatbot delayed
Chatbot/LLM work remains delayed until the modular content model, seed discipline, and safety guardrails are stronger.
