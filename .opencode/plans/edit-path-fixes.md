# Plan: edit-path fixes (net movement + corruption guard)

Status: **Implemented** — executed after user approval.

## Implementation Record

- **Net-movement edits:** `Purchases/Edit` + `Wastages/Edit` saves group old/new base per product and move only the diff (header-only edits touch zero stock; partial edits move the difference; block/check semantics preserved; `lockForUpdate` added on negative-diff products in Purchases/Edit).
- **`Unit::baseQtyPlausible()`** (tolerance `max(0.05, 1%)`, unknown units pass) guards both Edit saves with a delete-and-recreate message; **`Unit::storedBaseQty()`** lets all four delete paths self-heal to recomputed base instead of under-reverting (deletes still always succeed — blocking there would strand the admin).
- **Tests:** 6 new (header-only skip ×2, diff-only move, corrupt blocked ×2, corrupt delete heals). Suite 428/428 ✓ (1452 assertions), pint clean ✓. One fix mid-flight: missing `Unit` import in `Purchases/Index`. No migrations, no build.

## 1. Goal

Two sharp edges in purchase/wastage edits, no redesign: (1) header-only edits must not touch stock; (2) re-apply must refuse corrupt stored `base_qty` instead of amplifying it ~1000×.

## 2. Standing rules (do NOT forget)

- Every change tested (Pest). Pint after PHP changes. No migrations.
- **Phase 2 — Live Database Rules** (`AGENTS.md:184-190`): NEVER `migrate:fresh/refresh/reset`, `db:wipe`.
- Delete paths untouched (delete-clamp stays); edit-block philosophy stays.

## 3. Fix 1 — net-movement edits (Purchases/Edit + Wastages/Edit saves)

After validation: group old items by product → `oldBase` (stored `base_qty`, qty fallback); convert new rows → `newBase` (`toBaseQty`); per product `diff = new − old` (`> 0` increment, `< 0` coverage-check then decrement, `== 0` no stock call). Delete old, create new, update header unchanged. Add `lockForUpdate` on negative-diff products in Purchases/Edit.

## 4. Fix 2 — corruption guard

`Unit::baseQtyPlausible(unit, qty, base)`: recompute expected via `toBaseQty`, allow `max(0.05, 1%)` drift; unknown units pass. Called everywhere old `base_qty` is consumed (both Edit saves, both deletes' revert/restore). Failure → `ValidationException` naming product + line, advising delete-and-recreate.

## 5. Tests

Header-only edit on blocked stock succeeds untouched; partial edit moves diff only; corrupted fixture blocked (edit + delete); healthy rows pass. Full suite + pint.
