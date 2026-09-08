# Plan: pre-live consolidation (squash + seeds + rules)

Status: **Planned — NOT executed.** Do not execute until user says so.

## 1. Premise (verified, do NOT forget)

- App is NOT live for public; zero real orders anywhere (confirmed by user after a live-discipline stress test).
- 9 local commits ahead of `origin/main`; every Phase-2 file is unpushed. Nothing pushed is modified (verified: zero `M` entries under `database/migrations` vs `origin/main`; base tables come from pushed, untouched creates).
- Removing unpushed files = `git rm` (safe for prod). Pushed history stays immutable.
- `tests/Pest.php` runs `RefreshDatabase` WITHOUT seeders — the test DB gets reference rows ONLY from migrations. This killed the first draft (see §7).

## 2. Standing rules (do NOT forget)

- **Phase 2 — Live Database Rules** (`AGENTS.md:184-190`) are SUSPENDED by this plan and rewritten (see §6) — they assumed live orders that don't exist.
- Local `migrate:fresh --seed` is explicitly allowed by this plan (dev DB only; `DatabaseSeeder` restores admin `admin`/`password` + catalog).
- No `migrate:fresh` anywhere near production, ever. Prod path stays `migrate --force`.
- Related plans: all `.opencode/plans/*` (implemented) — behavior unchanged by this plan; it only reorganizes schema delivery + seeds.

## 3. Delete outright (unpushed, purpose evaporates without live rows)

- `072148_repair_pre_seed_kg_stock` (nothing to repair).
- `120601_drop_unused_purchase_wastage_unit_refs` (columns never exist post-squash).
- `072147_backfill_products_base_unit`, `053809_backfill_products_low_stock` (replaced by seeder/factory defaults).
- `070419_seed_purchase_units`, `121042_seed_base_unit_rows` (replaced by ONE consolidated seed migration + `UnitSeeder` mirror, see §4).
- `docs/phase2-unpushed.sql`, `docs/phase2-delivery-slot-fix.sql` (retire both; `migrate --force` is the only deploy path — the SQL bypass caused 3 of 4 past data incidents).

## 4. Squash (delete N, write fewer clean files)

- **Delete** the 4 decimal alters (`054728-31`) — final types go directly into definitions below.
- **Delete** `053633` (conversion UPDATEs) — values move into `UnitSeeder` + the seed migration.
- **NEW `add_inventory_columns_to_products_table`** (`current_stock` decimal, `base_unit`, `low_stock`): replaces `072146` + `054728` + `053808`.
- **NEW `add_inventory_columns_to_order_items_table`** (`base_qty` only): replaces `060819`; also drops the dead `product_unit_id` lines from `053655` (same dead-column treatment as purchase/wastage; flag if user wants it kept).
- **NEW `add_inventory_columns_to_units_table`** (`base_unit`, `to_base_factor`, `is_base`, `purchase_unit`, `integer_only`): replaces `053633` + `121041`.
- **NEW `seed_inventory_reference_units`** (idempotent): display-unit configs (`1 kg/500 g/250 g/1 pc/dozen/bunch`), `kg` row, `g`/`piece`/`ml` base rows. REQUIRED as a migration (not seeder-only) because tests never run seeders.
- **Keep as-is**: the 5 create-tables (already final form), `basket_recipe`, `delivery_slot` (clean single-purpose).
- Net: ~13 files deleted, ~4 clean files written. Fresh timestamps sort after pushed creates; FK targets exist.

## 5. Seeders/factories (replacing deleted backfills/seeds)

- `UnitSeeder`: full explicit config for EVERY row (display units' base+factor — fixes the fresh-install ordering bug where UPDATEs ran before rows existed; base rows; `kg` row).
- `ProductSeeder`: add `base_unit` + `low_stock` + sample `current_stock` per product (`g→5000`, `piece→50`, `ml→5000` defaults — confirm or supply numbers), else a fresh seed yields an unsellable shop (stock defaults 0).
- `ProductFactory`: set `base_unit` (from unit), decimal `current_stock`, sensible `low_stock` (makes tests invariant-honest).

## 6. AGENTS.md + rules rewrite

- Replace the live-DB section with pre-live rules: squash allowed; freeze + additive-only takes effect automatically from the first real order onward.
- Deploy checklist (persists): backup → upload code + `public/build` → `migrate --force` → `optimize:clear` → `migrate:status` clean → test order → NO raw-SQL path.

## 7. Double-check record (what verification caught)

- First draft wrongly deleted both seed migrations; `Pest.php` (no seeder runs in tests) proves one consolidated seed migration must stay. Corrected above.
- Verified: no pushed migration modified; no app/seeder references to deleted files; no tests reference the SQL docs; `DatabaseSeeder` restores admin login on fresh.

## 8. Tests (Pest, sqlite memory)

- Rewrite the 2 tests that `require` deleted migration files (backfill + repair) into seeder-based assertions.
- Update the stale seed comment in `DecimalInventoryTest`.
- New: seed migration covers all reference rows; `ProductSeeder` rows carry base/low/stock. Full suite + pint.

## 9. Out of scope

- Any behavior change (code untouched except tests); pushed-history rewrite; storefront wiring; prod data (none exists); pre-existing tracked junk (`render-test.php`, `resolveSingleFileComponentPath(privacy-policy))`) — flag separately if wanted.
