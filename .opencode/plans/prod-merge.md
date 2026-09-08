# Prod Merge Plan (TWO files, additive-only — owner-locked)

## Governing rule (do NOT forget, overrides all earlier merge drafts)
Additive-only: update nothing that exists, add only what's missing. No
UPDATE/DELETE on business rows. The single deliberate exception: scoped
unit-row normalization + dead-column drop, both proven empty-or-absent-only.

## File 1: docs/phase2-schema.sql — match prod schema to local
- info_cards CREATE (absent-only); delivery_slot ADD (human-gated single
  statement, only if §0 shows it missing).
- Units canonical replace: DELETE the 10 canonical names only (nothing FKs
  units.id — verified; custom admin rows survive) + INSERT all 10 fixed.
- Dead order_items.product_unit_id + FK drop (last; error = already gone).
- 6 migration registrations, self-skipping. Old names untouched.

## File 2: docs/phase2-products.sql — catalog, skip-if-present
- INSERT IGNORE fruits/vegetables categories by slug (from local rows).
- 91 product INSERTs, each absent-only by slug (duplicates fully skipped —
  not even names touched, per owner call). New rows: local values with
  stock forced 0 + image forced null.
- 1 kg unit rows only for products left with zero units (price = row price).
- Verify counts. Supersedes docs/phase2-delta.sql (deleted, not kept
  alongside — two overlapping files invite running the wrong one).

## Status: BUILT 2026-09-08 (uncommitted)
