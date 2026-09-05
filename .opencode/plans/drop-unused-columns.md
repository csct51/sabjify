# Plan: remove unused columns (nothing pushed → squash, not drop-migrations)

Status: **Implemented** — executed after user approval.

## Implementation Record

- Edited untracked `053647_create_purchase_items_table` + `112334_create_wastage_items_table` (removed `product_unit_id` FK lines); deleted untracked `053624` + `065122` pair and stray repo-root `assertOk()` (a pasted shell error, 130 bytes).
- New guarded cleanup migration `2026_09_03_120601_drop_unused_purchase_wastage_unit_refs` (`hasColumn`-guarded `dropConstrainedForeignId` on both tables; reversible) — ran clean locally, no-op on prod.
- Code: fillables, phpdocs (`BelongsTo` imports retained — still used), both `productUnit()` relations, and 4× `'product_unit_id' => null` writes removed.
- Verification: grep clean outside cart/order/recipe/basket contexts; sqlite suite replays edited creates from scratch (proves coherence); `migrate --force` clean; full suite 370/370 ✓ (1254 assertions), pint clean ✓. Deleted files' rows in local `migrations` table are inert.

## 1. Context (do NOT forget)

- Last push was `deaec59`; ALL Phase 2 inventory work is uncommitted/untracked. So never-pushed junk columns are removed by **editing/deleting the migration files**, not by adding drop-migrations — prod will never see them.
- **Phase 2 — Live Database Rules** (`AGENTS.md:184-190`) still apply to everything else: NEVER `migrate:fresh/refresh/reset`, `db:wipe`. Additive migrations only + `migrate --force` on prod.
- **Plan workflow:** plans live in `.opencode/plans/` (no timestamp, just plan name). Ask before executing.

## 2. Audit result (verified via codebase-wide grep)

### Confirmed unused → remove

| Column | Evidence |
|---|---|
| `purchase_items.product_unit_id` | Written only as `null` (Purchases Create/Edit), never read in app/blades/tests |
| `wastage_items.product_unit_id` | Same — `null` only (Wastages Create/Edit), never read |
| `PurchaseItem::productUnit()` / `WastageItem::productUnit()` relations | Defined, zero callers (all live `productUnit` uses are cart/order/recipe/basket) |
| `053624_add_stock_quantity…` + `065122_remove_…` migration pair | Net-zero add→drop, both untracked — delete both files instead of replaying pointless DDL + `999` backfill on prod |
| Stray repo-root file literally named `assertOk()` | Junk, delete |

### Verified used → keep

- `product_unit_id` on cart/order items + recipe/basket pivots (live unit-picker flows, 30+ refs).
- `products.unit/price/mrp` — legacy but still read: `minPrice()`/`discountPercent()` fallbacks, ~10 blade fallbacks (`product-card`, `cart`, invoices…), forms, seeders. Removing = separate large refactor (offered as follow-up, not in scope).
- `unit_price` column does not exist (misleading filename `..._add_unit_price_...` actually adds `unit`+`price`, both used).
- `settings`, geo columns, descriptions, `product_name` snapshots, basket/recipe pivots — all live.

## 3. Execution checklist (for implementer, when approved)

1. Edit untracked `database/migrations/2026_09_02_053647_create_purchase_items_table.php` + `2026_09_02_112334_create_wastage_items_table.php`: remove the `product_unit_id` FK lines.
2. Delete untracked `2026_09_02_053624_add_stock_quantity_to_product_units_table.php` + `2026_09_02_065122_remove_stock_quantity_from_product_units_table.php`; delete stray repo-root `assertOk()`.
3. Guarded cleanup migration `drop_unused_purchase_wastage_unit_refs` (`Schema::hasColumn` check → `dropConstrainedForeignId` on both tables) — no-op on prod, syncs DBs that already ran the old files (local/staging).
4. Code: drop `'product_unit_id'` from `PurchaseItem`/`WastageItem` fillables + phpdocs, delete both `productUnit()` relations (+ unused `BelongsTo` imports), remove 4× `'product_unit_id' => null` writes (Purchases Create/Edit, Wastages Create/Edit).
5. Verify: grep clean for `product_unit_id` outside cart/order/recipe/basket contexts; sqlite suite replays edited migrations from scratch (proves coherence); `php artisan migrate --force` local; `php artisan test --compact`; `vendor/bin/pint --dirty --format agent`.
6. Deploy = normal push + `migrate --force` (prod creates tables without junk columns; cleanup migration skips).

## 4. Risks / notes

- Local `migrations`-table rows for deleted files are inert (Laravel only runs files present) — no action needed.
- `down()` of edited creates still drops whole tables — unchanged semantics.
- Commit only when explicitly requested.
