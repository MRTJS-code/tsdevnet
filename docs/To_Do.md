# To Do

## Purpose
Tracks progress against the expected development plan.

---

## Done
- Basic public profile/recruiter portal structure established
- Magic-link auth flow introduced
- Pending vs approved access concept established
- Admin area introduced
- Database-backed content/CMS direction introduced
- Rule-based assistant seam introduced
- Fixed hero and footer model implemented
- Ordered homepage module registry introduced
- Reusable content reset/seed flow introduced
- Local-only Tony example seed/reset templates documented
- Documentation discipline introduced for architecture, testing, decisions, debug, and AI contract

## In progress
- Consolidating admin around the new module hub while older typed payload editors still exist underneath
- Tightening migration/setup verification around the new module registry
- Replacing remaining older docs language that still refers to wrapper blocks as canonical

## Next
- Add automated tests around module repositories and homepage assembly
- Add integration checks for migration + reset + seed flow
- Clean up or retire older transitional homepage/content-block routes once no longer needed
- Improve admin messaging around typed payload editors linked from the module hub

## Later
- Improve recruiter portal UX further
- Richer recruiter CRM/admin fields
- Handoff/admin reply workflows
- Better mobile admin usability
- Notifications/polling improvements
- Safe chatbot teaser or gated assistant
- AI provider integration once content governance is stable
- JD comparison once retrieval/guardrails are mature

## Remaining Phase 1 focus
1. Finish test coverage for the modular homepage model
2. Finish admin simplification and remove transitional CMS clutter
3. Keep setup/reset discipline strong for public vs local content
4. Keep AI features bounded until content structure is stable
