# Plan: pre-live consolidation (squash + seeds + rules)

Status: **EXECUTED 2026-09-08 (uncommitted working tree).** `migrate:fresh --seed` clean on local MySQL; full suite 436/436 green; pint clean. See §10 for deviations found during execution.

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
- Sequencing with `product-import.md`: consolidation FIRST (this plan), import SECOND. The import's only hard dependency is §4's `seed_inventory_reference_units`. CSV upload itself can happen anytime.

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
- `ProductSeeder`: SUPERSEDED by `product-import.md` — do NOT write sample products. The import seeder (`ProductImportSeeder`, owner CSV, replace-all scope) takes this slot in `DatabaseSeeder` instead. Only constraint from this plan: it runs after §4's `seed_inventory_reference_units`.
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
- New: seed migration covers all reference rows; product-seed assertions live in `product-import.md`'s `ProductImportTest` (fixture CSV), not here. Full suite + pint.

## 10. Execution deviations (2026-09-08 — do NOT forget why)

- "5 create-tables final form" was wrong for 3: `purchase_items` / `wastage_items` / `wastages` creates still carried integer qty columns. Baked decimals directly into those (unpushed) creates instead of keeping them as-is. Final schema identical.
- `053655` (order_items.product_unit_id) deleted ENTIRELY, not edited — verified zero writes anywhere (only Fillable + PHPDoc referenced it; both stripped from `OrderItem`).
- Replaced-file deletions the plan implied but didn't list: `072146`, `053808`, `060819`, `121041` (superseded by the 4 new files). First fresh-run failed on this; deleted, re-ran clean.
- Seed migration covers ONLY purchase + base rows (`kg/piece/g/ml`). Seeding display rows collided with 36 tests' `Unit::create` setup (UNIQUE units.name) — display rows stay owned by UnitSeeder + tests, exactly like prod (admin-managed).
- `ProductFactory` derives `base_unit`/`low_stock` in `configure()->afterCreating` via `array_key_exists` (distinguishes "not passed" from "explicit null" so the legacy-fallback test keeps working). Definition-time derivation was tried first and broke 18 tests (random pick vs caller `unit` override mismatch).
- 5 basket tests needed honest setup: pinned `unit => '1 kg'` + matching `units` lookup row (base-share validation now fires for real instead of passing vacuously on null base). No validation behavior changed.
- AGENTS.md also gained the SFC convention line (single-file for new small components only) in the same edit.

## 9. Out of scope

- Any behavior change (code untouched except tests); pushed-history rewrite; storefront wiring; prod data (none exists); pre-existing tracked junk (`render-test.php`, `resolveSingleFileComponentPath(privacy-policy))`) — flag separately if wanted.
