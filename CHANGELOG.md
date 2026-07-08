# Changelog

All notable changes to the Homestead module are documented in this file.

## [Unreleased] — 2026-07-06

### Fixed

- **Editor unsaved-state on save** — Removed premature `markClean()` when the save form submits. The unsaved-changes badge and `beforeunload` guard now stay active until the page reloads after a successful save, so a failed or interrupted save no longer clears the dirty state early (QA observation #3).
- **Editor layout payload validation** — Reject non-object JSON layouts (e.g. indexed arrays like `[1, 2, 3]`) at the controller before calling the service. Empty objects and valid surface field maps still pass (QA E-02 / V-03).
- **Editor placements payload validation** — Reject non-array JSON and associative objects for placements; only sequential JSON arrays are accepted, including empty `[]` (QA V-02).

### Changed

- **Rank-based unlimited homestead slots** — Replaced the broad `isStaff` slot bypass with the `unlimited_homestead_slots` rank power. Attach it via Admin → User Ranks to grant unlimited room/house creation; regular members keep base + activated slot item limits. Configurable via `unlimited_slots_power` in `config/lorekeeper/homestead.php`.
- **Configurable house editor background** — Removed the hardcoded outdoor canvas gradient from CSS. House editor backgrounds are configured in `canvas_backgrounds.outdoor` and can be overridden via Admin → Site Images (`homestead_house_editor_bg`). Indoor room backgrounds are unchanged.
- **Editor layer controls** — Per-item forward/backward layer buttons on selection; swap-based z-order with normalization to prevent duplicate z-index values. Toolbar layer buttons use the same logic.
- **Editor layer button clicks** — Fixed per-item ↑/↓ controls not firing because `preventDefault()` on `mousedown` suppressed the `click` event; buttons disable when no adjacent layer exists (e.g. single placed item).

### QA

- Full module QA completed; see [QA_REPORT.md](QA_REPORT.md).
- No additional functional bugs were found beyond the items fixed above.
