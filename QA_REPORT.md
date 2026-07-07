# Homestead Module — QA Report

**Date:** 2026-07-06  
**Environment:** Local dev (`http://127.0.0.1:8000`)  
**Database:** `malcians` (MySQL)  
**Tester:** Automated service tests, HTTP checks, browser UI verification (admin user)

---

## Executive Summary

| Area | Result | Notes |
|------|--------|-------|
| Navigation | **PASS** | Sidebar, navbar dropdown, rooms/houses routes |
| CRUD (Rooms & Houses) | **PASS** | Create, rename, delete verified via service layer |
| Room Editor | **PASS** | Load, inventory, place, drag, surfaces, save, reload |
| House Editor | **PASS** | Load with 8 placements, inventory groups present |
| Inventory integration | **PASS** | Owned homestead items appear in correct tabs |
| Drag & Drop | **PASS** | Position updates without full re-render |
| Save / Load | **PASS** | Round-trip to DB confirmed |
| Error cases | **PASS** | Invalid items, bounds, quantities, layout keys rejected |
| Authorization | **PASS** | Ownership + space-type enforced |
| Validation | **PASS** | Name rules, JSON payloads, placement schema |

**Overall: PASS** — No blocking bugs found. No code changes were required.

---

## Test Environment

| Item | Value |
|------|-------|
| App URL | `http://127.0.0.1:8000` |
| Test user | `admin` (id: 1, `malcianemail@gmail.com`) |
| Test room | id: 1, `Test Room`, type `indoor` |
| Test house | id: 2, `my house`, type `outdoor` |
| Homestead items in catalog | 4 items (Bread, Iron, Wood, Wheat) |
| Middleware | `auth`, `verified`, `alias` (via `routes/web.php`) |

---

## 1. Navigation

| # | Test Case | Method | Expected | Result |
|---|-----------|--------|----------|--------|
| N-01 | Guest access `/homestead/rooms` | HTTP | 302 redirect to login | **PASS** |
| N-02 | Guest access `/homestead/rooms/1/editor` | HTTP | 302 redirect to login | **PASS** |
| N-03 | Guest access `/homestead/rooms/create` | HTTP | 302 redirect to login | **PASS** |
| N-04 | Authenticated rooms list | Browser | 200, title "Homestead :: Rooms" | **PASS** |
| N-05 | Authenticated houses list | Browser | 200, title "Homestead :: Houses" | **PASS** |
| N-06 | Sidebar links (Homestead → Rooms / Houses) | Browser | Links present and navigable | **PASS** |
| N-07 | Navbar Homestead dropdown | Code review | Links to rooms and houses | **PASS** |
| N-08 | Breadcrumbs on list pages | Code review | Homestead → Rooms/Houses | **PASS** |
| N-09 | Editor exit link | Browser | Returns to correct list segment | **PASS** |

---

## 2. CRUD (Rooms & Houses)

| # | Test Case | Method | Expected | Result |
|---|-----------|--------|----------|--------|
| C-01 | Open create room modal (`GET /homestead/rooms/create`) | Code review | 200 partial with form | **PASS** |
| C-02 | Open edit room modal | Code review | 200 with room name | **PASS** |
| C-03 | Open delete room modal | Code review | 200 with confirm text | **PASS** |
| C-04 | Create room (valid name) | Service | Room + layout row created | **PASS** |
| C-05 | Rename room | Service | Name updated in DB | **PASS** |
| C-06 | Delete room | Service | Soft-deleted, placements/layout removed | **PASS** |
| C-07 | Create with name too short (`ab`) | Validation rules | Rejected (`between:3,100`) | **PASS** |
| C-08 | Create house flow | Service (same controller) | Shared `SpaceController` with `outdoor` type | **PASS** |
| C-09 | Modal cancel button | Code review | `data-dismiss="modal"` present | **PASS** |
| C-10 | Submit loading state on modal forms | Code review | Spinner + disabled submit | **PASS** |
| C-11 | Validation errors re-open modal | Code review | `old('name')` + error alert on list page | **PASS** |

**Note:** Slot-limit blocking (`403` on create modal) was not exercised — all sampled users are staff with unlimited slots (`bypass_slot_limits_for_staff: true`).

---

## 3. Room Editor

| # | Test Case | Method | Expected | Result |
|---|-----------|--------|----------|--------|
| RE-01 | Load editor `/homestead/rooms/1/editor` | Browser | 200, editor shell renders | **PASS** |
| RE-02 | `HomesteadRoomEditor` initializes | CDP | `window.homesteadEditor` exists | **PASS** |
| RE-03 | Loading overlay hides after init | CDP | `#homesteadEditorCanvasLoading` has `d-none` | **PASS** |
| RE-04 | Initial placements from DB | CDP / DB | 2 placements loaded (later 3 after QA save) | **PASS** |
| RE-05 | Catalog embedded in page | CDP | 4 catalog keys | **PASS** |
| RE-06 | Furniture inventory tab | CDP | 3 placeable items | **PASS** |
| RE-07 | Surfaces inventory tab | CDP | 2 selectable surface items | **PASS** |
| RE-08 | Empty canvas placeholder | Browser | Shown when no furniture placed | **PASS** |
| RE-09 | Click-to-place furniture | CDP | Placement count 2 → 3, `isDirty: true` | **PASS** |
| RE-10 | Drag placed item (position update) | CDP | CSS `left`/`top` updated without full re-render | **PASS** |
| RE-11 | Apply surface selection | CDP | `flooring_item_id` set in layout payload | **PASS** |
| RE-12 | Unsaved changes badge | Code review | Shown on dirty state | **PASS** |
| RE-13 | Save button loading state | Browser | Shows "Saving...", disabled on submit | **PASS** |
| RE-14 | Save persists to database | DB query | 3 placements + `flooring_item_id = 2` | **PASS** |
| RE-15 | Reload restores saved state | CDP after reload | 3 placements, layout `{flooring_item_id:2}` | **PASS** |
| RE-16 | Mobile inventory toggle | Code review | `#toggleInventoryButton` + close button | **PASS** |

---

## 4. House Editor

| # | Test Case | Method | Expected | Result |
|---|-----------|--------|----------|--------|
| HE-01 | Load editor `/homestead/houses/2/editor` | Browser | 200, title includes house name | **PASS** |
| HE-02 | Editor initializes | CDP | `homesteadEditor` present | **PASS** |
| HE-03 | Placements load | CDP / Service | 8 placements | **PASS** |
| HE-04 | Outdoor inventory groups | Service | Furniture + surfaces populated | **PASS** |
| HE-05 | Outdoor canvas background | Code review | `homestead-editor-canvas-house-bg` class | **PASS** |
| HE-06 | Shared editor JS/CSS | Code review | Same `homestead-room-editor.js` for both types | **PASS** |

---

## 5. Inventory Integration

| # | Test Case | Method | Expected | Result |
|---|-----------|--------|----------|--------|
| I-01 | Only owned items shown | Service | Aggregated `user_items` quantities | **PASS** |
| I-02 | Only homestead-configured items | Service | `placeableInHomestead()` scope applied | **PASS** |
| I-03 | Items grouped by placement type | Service | Furniture vs surfaces tabs | **PASS** |
| I-04 | Quantity display (`xN` / `available / total`) | Code review + CDP | Updates on place/remove | **PASS** |
| I-05 | Unavailable when all copies placed | Code review | `is-unavailable` class + tooltip | **PASS** |
| I-06 | Empty inventory message | Code review | Hint text per space type | **PASS** |
| I-07 | Catalog includes placed-but-unowned-in-tab items | Service | Orphan placements still render | **PASS** |

**Test data (room id 1):**

| Item | placement_type | Furniture tab | Surfaces tab |
|------|----------------|-------------|--------------|
| Bread (1) | decoration | Yes | No |
| Iron (2) | floor | Yes | Yes |
| Wood (4) | decoration | Yes | No |
| Wheat (5) | flooring | No | Yes |

Items with `placement_type: floor` intentionally appear in **both** furniture and surfaces groups per `config/lorekeeper/homestead.php`.

---

## 6. Drag & Drop

| # | Test Case | Method | Expected | Result |
|---|-----------|--------|----------|--------|
| D-01 | Drag inventory item to canvas | Code review | `placeItemFromClientPoint` on mouseup | **PASS** |
| D-02 | Drag placed item on canvas | CDP | Position updates via `updatePlacementPosition` | **PASS** |
| D-03 | Drag uses rAF throttling | Code review | `scheduleDragPositionUpdate` | **PASS** |
| D-04 | Click without drag still places | Code review | 4px movement threshold | **PASS** |
| D-05 | Cannot drag unavailable items | Code review | `is-unavailable` guard | **PASS** |
| D-06 | Selection controls (z-order, delete) | Code review | Toolbar buttons on selected item | **PASS** |
| D-07 | Delete/Backspace removes selection | Code review | Keydown handler | **PASS** |

---

## 7. Save & Load

| # | Test Case | Method | Expected | Result |
|---|-----------|--------|----------|--------|
| SL-01 | Service save round-trip (unchanged data) | Service | `saveEditorState` returns true | **PASS** |
| SL-02 | HTTP POST save (valid payload) | Browser + DB | Redirect + data persisted | **PASS** |
| SL-03 | Placements bulk insert | Code review | Chunked `RoomPlacement::insert()` | **PASS** |
| SL-04 | Surface layout saved to `room_layouts` | DB | `flooring_item_id` updated | **PASS** |
| SL-05 | Editor reload reads placements ordered by `z_index` | Service | Eager-loaded relation | **PASS** |
| SL-06 | Success flash message | Code review | `save_message` from config | **PASS** |
| SL-07 | Server-side dimensions used on save | Code review | Client `width`/`height` ignored | **PASS** |

---

## 8. Error Cases

| # | Test Case | Method | Expected | Result |
|---|-----------|--------|----------|--------|
| E-01 | Invalid placements JSON (`not-json`) | Controller | Flash error, redirect back | **PASS** (code path) |
| E-02 | Invalid layout JSON (non-object array) | Controller | Flash error, redirect back | **PASS** (code path) |
| E-03 | Invalid item id (999999) | Service | Save rejected | **PASS** |
| E-04 | Item outside canvas bounds | Service | Save rejected | **PASS** |
| E-05 | Quantity overflow (more placed than owned) | Service | Save rejected | **PASS** |
| E-06 | Unknown layout field key | Service | Save rejected | **PASS** |
| E-07 | Unowned surface item | Service | Save rejected | **PASS** |
| E-08 | Flooring-only item as furniture (Wheat) | Service | Save rejected | **PASS** |
| E-09 | Missing `z_index` in placement | Service | Save rejected | **PASS** |
| E-10 | `z_index` of 0 | Service | Save rejected | **PASS** |
| E-11 | Service error surfaced to user | Code review | `FlashesServiceErrors` trait | **PASS** |

---

## 9. Authorization

| # | Test Case | Method | Expected | Result |
|---|-----------|--------|----------|--------|
| A-01 | Other user cannot load room editor | Service / design | 404 via `resolveOwnedSpace` | **PASS** |
| A-02 | Other user cannot save editor state | Service | `assertUserOwnsSpace` rejects | **PASS** |
| A-03 | Other user cannot update room | Service | `getUserRoom` returns null | **PASS** |
| A-04 | Other user cannot delete room | Service | Delete fails | **PASS** |
| A-05 | Edit house via `/homestead/rooms/edit/{houseId}` | Service | `getUserRoom` with type filter returns null | **PASS** |
| A-06 | POST edit cross-type URL | Design | `resolveOwnedSpace` before mutate → 404 | **PASS** |
| A-07 | Defense-in-depth in save paths | Code review | `assertUserOwnsSpace` in editor service | **PASS** |
| A-08 | CSRF protection | Framework | Laravel `web` middleware + `@csrf` forms | **PASS** |

---

## 10. Validation

| # | Test Case | Method | Expected | Result |
|---|-----------|--------|----------|--------|
| V-01 | Room/house name `required\|between:3,100` | Model rules | Enforced on create/edit POST | **PASS** |
| V-02 | Placements must be JSON array | Controller | Decoded + `is_array` check | **PASS** |
| V-03 | Layout must be JSON array/object | Controller | Decoded + `is_array` check | **PASS** |
| V-04 | Furniture placement types only on canvas | Service | Group filter in `persistPlacements` | **PASS** |
| V-05 | Surface field ↔ placement type mapping | Service | `surfaceLayoutFields` validation | **PASS** |
| V-06 | Canvas boundary checks | Service | Config canvas width/height | **PASS** |
| V-07 | Modal form field errors | Blade | `is-invalid` + `$errors` display | **PASS** |

---

## Observations (Non-blocking)

1. **No automated test suite** — There are no PHPUnit/Feature tests under `tests/` for Homestead. Recommend adding tests for authorization, validation, and save round-trips.

2. **Slot limits untested end-to-end** — `bypass_slot_limits_for_staff` is enabled and sampled users are staff. Non-staff slot enforcement should be tested in staging with a regular member account.

3. **Optimistic dirty-state clear on save** — The editor calls `markClean()` when the save form submits, before the server responds. If the request fails, the page reloads on redirect anyway; a rare network failure without navigation could leave the UI without an unsaved warning.

4. **Shared `floor` placement type** — Items like Iron (`placement_type: floor`) appear in both Furniture and Surfaces tabs by configuration. This is intentional but may confuse users; document in user-facing help if needed.

5. **PHP deprecation noise** — Laravel 8 on modern PHP emits deprecation warnings in CLI/tinker; unrelated to Homestead but visible during QA.

6. **No API/rate limiting** — Editor save is a full form POST with no throttle. Acceptable for current scope.

---

## Bugs Found

**None.** No code modifications were made during this QA pass.

---

## Files Reviewed

| Area | Paths |
|------|-------|
| Routes | `routes/lorekeeper/homestead.php` |
| Controllers | `app/Http/Controllers/Homestead/*` |
| Services | `app/Services/Homestead/*` |
| Models | `app/Models/Homestead/*` |
| Views | `resources/views/homestead/*` |
| Frontend | `public/js/homestead-room-editor.js`, `public/css/lorekeeper.css` |
| Config | `config/lorekeeper/homestead.php` |

---

## Recommendations

1. Add `tests/Feature/Homestead/` covering CRUD, editor save, authorization, and validation.
2. Test slot limits with a non-staff user when `bypass_slot_limits_for_staff` is disabled.
3. Add browser E2E tests (Dusk or Playwright) for drag-and-drop if regressions become frequent.
4. Consider deferring `markClean()` until a successful save response for stricter unsaved-state UX.

---

## Sign-off

| Role | Status |
|------|--------|
| Functional QA | **PASS** |
| Security QA | **PASS** |
| Ready for merge | **Yes** (no blockers) |
