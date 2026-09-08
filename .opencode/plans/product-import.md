# Product Import Plan (mandi CSVs → seed, replaces samples)

## Goal
Replace all 25 sample products with the real catalog merged from the owner's
mandi price sheets. Products only — price/unit are placeholders for admin to
enter manually.

## Source analysis (verified, not assumed)
- `docs/Book1.csv` (cp1252, 11 cols): 49 named rows → 46 unique (Apple×3,
  Anar×2 are mandi grade rows — dedupe keep-first). ~950 blank filler rows.
- `docs/Book2.csv` (cp1252, 34 cols, multiline headers): 65 named rows, unique.
- Overlap: 19. Merged total ≈ 92 (dedupe case-insensitive + trimmed).
- Exact duplicates → ONE copy (Book1 row wins ties).
- Near-duplicates KEPT separate for admin to merge in UI: Papaya/Papita,
  Bottle Gourd variants, 4 gingers, Kheksi/Kakdi (Kheksi). Auto-merging guesses
  corrupt the catalog.
- Quirks: truncated `Broad Beans (Sem/Seem`, junk control char in
  `Brinjal Gulabi (Pink)` (sanitized at merge), names kept raw with
  parenthetical aliases (searchable; admin edits later).

## Category split (owner: fruit→Fruits, veg→Vegetables, admin re-sorts)
- Existing `Fruits` / `Vegetables` categories reused (no new categories).
- Fruits (14): Aalu Bukhara, Amrud, Anar, Apple, Avocado imported, Banana,
  Kafir lime, Lemon, Mango, Nariyal Pani (Bangalore), Nariyal Pani (Kerala),
  Naspati, Papaya, Papita.
- Everything else → Vegetables. Judgment calls: Cherry tomato, Raw Banana,
  Sweet Corn → Vegetables (culinary use).

## Per-product spec (uniform, owner-specified)
`base_unit=g`, `unit='1 kg'`, ONE `product_units` row (1 kg @ price 100,
mrp null, `in_stock=true` so the first purchase immediately sells — displays
still show Out of Stock at zero stock), legacy `price=100`,
`current_stock=0`, `low_stock=1000`, active, unfeatured, imageless (generic
fallback), generic description, slug from raw name via `Str::slug`.

## Implementation
1. Merge sources → committed UTF-8 `database/seeders/data/products.csv`
   (`name,category` only). `docs/Book*.csv` stay as source artifacts.
2. Rewrite `ProductSeeder` to read the merged CSV (fgetcsv, no new deps,
   `updateOrCreate` by slug). No `ProductImportSeeder` — the old placeholder
   idea is dead; ProductSeeder IS the import seeder.
3. `BasketSeeder` / `RecipeSeeder`: seed shells with ZERO product links
   (their sample-only slugs no longer exist). Rewrite `BasketSeederTest` /
   `RecipeSeederTest` to assert shells + idempotent re-seed.
4. `migrate:fresh` FIRST on empty schema, then `--seed` (never db:seed on
   top of old data; local dev only — allowed pre-live).
5. Verify ~92 products + shells, full suite + pint.

## Status: EXECUTED 2026-09-08 (see execution notes in chat)
