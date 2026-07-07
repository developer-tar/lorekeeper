# Changelog

All notable changes to the Homestead module are documented in this file.

## [Unreleased] — 2026-07-06

### Fixed

- **Editor unsaved-state on save** — Removed premature `markClean()` when the save form submits. The unsaved-changes badge and `beforeunload` guard now stay active until the page reloads after a successful save, so a failed or interrupted save no longer clears the dirty state early (QA observation #3).
- **Editor layout payload validation** — Reject non-object JSON layouts (e.g. indexed arrays like `[1, 2, 3]`) at the controller before calling the service. Empty objects and valid surface field maps still pass (QA E-02 / V-03).
- **Editor placements payload validation** — Reject non-array JSON and associative objects for placements; only sequential JSON arrays are accepted, including empty `[]` (QA V-02).

### QA

- Full module QA completed; see [QA_REPORT.md](QA_REPORT.md).
- No additional functional bugs were found beyond the items fixed above.
