# Product Import Plan (CSV → seed, replaces current samples)

## Goal
Replace all current sample products with the owner's real product list, via a committed CSV + one-off seeder. Replace-all scope (existing samples deleted, list becomes the only seed data).

## Ordering vs consolidation
- Requires consolidated units first: `seed_inventory_reference_units` (kg/g/piece/litre/ml rows) must exist before this seeder runs.
- This plan supersedes the `ProductSeeder` placeholder section of `consolidation.md` — do NOT write placeholder products; write the import seeder instead.

## CSV contract
- Location: `database/seeders/data/products.csv` (committed to repo; UTF-8, header row, comma-separated).
- Status: NOT YET UPLOADED. Column mapping below is the proposal — finalize against actual headers once the file lands, then update §Mapping before executing.

### Proposed columns (flexible order, exact headers TBD on inspection)
| CSV column (proposal) | Maps to | Notes |
|---|---|---|
| `name` | `products.name` | Required, unique, trim; Hindi/English as given |
| `category` | `categories` lookup/create | Optional; if absent, seed uncategorized (nullable FK) |
| `base_unit` | `products.base_unit` | `g`, `piece`, or `ml` only; default `g` if blank |
| `purchase_qty` + `purchase_unit` | `product_units.qty/unit` | e.g. `1,kg`; default `1,kg` for g-base, `1,piece` for piece-base, `1,litre` for ml-base |
| `price` | `product_units.price` (paise) | Required; CSV in ₹, seeder ×100 |
| `mrp` | `product_units.mrp` | Optional; blank = null |
| `stock` | `products.current_stock` | In BASE units (g/piece/ml); blank = 0 |
| `low_stock` | `products.low_stock` | In PURCHASE units (admin convention); blank = default (1000/10/1000) |
| `description` | `products.description` | Optional; blank = null |
| `image` | filename only | Optional; matches a file in `storage/app/public/products/` uploaded separately; blank = generic image |

## §Mapping (FINAL — fill after CSV inspection)
- Actual headers: _TBD_
- Deviations from proposal: _TBD_
- Row count: _TBD_

## Seeder design
- New `database/seeders/ProductImportSeeder.php`, called from `DatabaseSeeder` INSTEAD of sample-product seeding.
- Pure PHP `fgetcsv` (no new dependencies).
- Per row: resolve/create category → `Product::create` (`base_unit`, `current_stock`, `low_stock`, description, image) → `product_units` row (`qty/purchase_unit/price/mrp`, `in_stock = stock > 0`).
- Validation per row: unknown base_unit, non-numeric/negative price, missing name → collect and report; FAIL the seed loudly (throw with row numbers) rather than silently skipping, so bad data never ships.
- Idempotent-ish: seeder truncates `products`/`product_units` first (fresh-seed context only — never run against live).

## Tests
- `tests/Feature/ProductImportTest.php` with a 5-row fixture CSV in `tests/Fixtures/products-sample.csv` covering: g-base kg price, piece-base, ml-base litre price, blank-optionals row, FREE price 0 row.
- Asserts: product count, base_unit values, price paise conversion, stock values, `in_stock` flags, category creation.
- Bad-row test: malformed row (negative price) throws with row number.

## Execution steps
1. Owner drops CSV → inspect headers/count → fill §Mapping above.
2. Run consolidation units migration first (or full `migrate:fresh --seed` on the consolidated tree).
3. Write seeder + fixture + test; run `php artisan test --compact --filter=ProductImportTest`.
4. `migrate:fresh --seed` locally, spot-check storefront + admin (prices, packs, stock pills).
5. Full suite + pint, then deploy checklist (code + build + `migrate --force`).

## Status: NOT STARTED (blocked on CSV upload + §Mapping)
