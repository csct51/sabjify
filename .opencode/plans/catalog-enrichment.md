# Catalog Enrichment Plan (no fresh)

## Goal
Alternate Hindi/Hinglish names for all 91 products, dummy product links in
baskets/recipes for admin to refine, no per-product images (generic fallback
stays). All applied WITHOUT migrate:fresh — re-runnable seeders only.

## Decisions (owner-locked, do NOT forget)
- Hindi source: agent drafts `hinglish, हिंदी` into the CSV; owner corrects.
  Exotic/herb transliterations (asparagus, celery, lolorossa…) are best-effort.
- Dummy links: agent's sensible pick of verified catalog slugs; admin changes.
- Images: nothing to do.
- Never clobber: alternate-names backfill and dummy attaches are fill-if-empty
  only — admin edits and admin links are never touched, re-runs are no-ops.

## Implementation
1. `products.csv` gains `alternate_names` (done 2026-09-08, 91 rows).
2. `ProductSeeder`: header `name,category,alternate_names`; sets names on
   create + backfills when existing row is empty.
3. `BasketSeeder`/`RecipeSeeder`: `DUMMY_LINKS` slug map + attach-if-empty
   (baskets: plain sync of found slugs; recipes: pivot defaultUnit id).
4. Tests: new `ProductCatalogSeedTest` (counts, tamatar alternates, dummy
   links, re-seed preserves admin link); shell assertions → dummy assertions.
5. Apply on dev DB via `db:seed --class=` (NO fresh); full suite + pint.

## Status: EXECUTED 2026-09-08 (uncommitted)
