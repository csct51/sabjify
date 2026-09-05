# Plan: enum → DB-driven base units (`is_base` column)

Status: **Implemented** — executed after user approval.

## Implementation Record

- **Migrations shipped:** `2026_09_03_121041_add_base_config_to_units_table` (DDL: `is_base`, `purchase_unit` nullable, `integer_only` default false) + `2026_09_03_121042_seed_base_unit_rows` (DML: flags `piece`, inserts `g`/`ml` base rows, idempotent). Ran clean via `migrate --force`.
- **Models:** `Unit` enum cast dropped (plain string) + `scopeIsBase()`, `baseRow()`, `purchaseUnitOptions()`, `integerOnlyFor()`, `qtyHintFor()`; `Product::baseUnit(): ?string` (column-first, derivation fallback), `purchaseUnit()`/`displayStock()` generic (`2.5 kg (2500 g)`, `1.5 litre (1500 ml)`); `app/Enums/BaseUnit.php` deleted.
- **Units UI:** dynamic base selects, Is-base + Purchase Unit + Whole-numbers-only inputs, unflag/rename/delete guards while referenced (incl. `Units/Index::delete` base guard).
- **ProductForm:** dynamic base select, persists string `base_unit`, strict same-base validation retained.
- **Purchases/Wastages:** unit lists, integer rules, whole-assertions, and all 4 qty hints now read base rows (no `=== 'piece'` remnants).
- **Dev notes** rewritten (units create/edit, product-form). Seeder/factory updated.
- **Tests:** 6 new (seed config, unknown-base rejection, UI-only `meter` end-to-end, unflag/delete guards, enum-absence) + `->value` updates + `AdminSettingsTest`-safe (seeded `g` row). Suite 376/376 ✓ (1291 assertions), pint clean ✓. Fixes applied mid-flight: `purchase_unit` nullable (backend defaults blank→name), Edit test passes model not id.
- Related plans executed first: `drop-unused-columns` (zero column overlap, verified).

## 1. Goal (user-locked)

Shift base units from the `App\Enums\BaseUnit` PHP enum to the database: an `is_base` column in the `units` table, so creating a new base unit in future is an admin data operation (no code deploy). Delete the enum file.

## 2. Standing rules (do NOT forget)

- **Phase 2 — Live Database Rules** (`AGENTS.md:184-190`): NEVER `migrate:fresh/refresh/reset`, `db:wipe`. Additive `Schema::table` migrations only + `php artisan migrate --force` on production. New columns nullable/safe defaults; no history rewrite (orders/baskets/carts untouched).
- **Plan workflow:** plans live in `.opencode/plans/` (no timestamp, just plan name). Finish plan file first, then **ask whether to execute; only execute on explicit yes**.
- **Push state (shared with drop-unused-columns plan):** nothing uploaded to production since `deaec59` — all Phase 2 migrations are unpushed. Prefer editing/deleting unpushed migration files over new drop-migrations.
- **Related plan:** `.opencode/plans/drop-unused-columns.md` — independent (touches purchase/wastage item FK columns; this plan touches `units`/`products` + validation). Recommended order: drop-unused-columns first, so this plan's implementation and test greps run against the final schema.
- **Developer note (permanent, rewritten by this plan):** OLD — `To add a new base unit, add a new case to BaseUnit enum.` (in `app/Enums/BaseUnit.php` docblock — file gets deleted — plus `units/create:30`, `units/edit:30`, `product-form:59`). NEW — `To add a base unit: create (or tick) a Unit row as Is-base with its purchase unit.`

## 3. Locked decisions (user-confirmed)

- New columns on `units`: `is_base` bool + `purchase_unit` string nullable + `integer_only` bool default false. Why both extras, in one line: the enum's remaining jobs are "which unit do we ask qty in" (`g`→`kg`) and "whole numbers only?" (`piece`→yes) — each base row must carry those facts or future bases can't work admin-only.
- Base rows: `g` (purchase `kg`, decimal), `piece` (purchase `piece`, integer-only), `ml` (purchase `litre`, decimal) — all with `base_unit = null` (user call). `g`/`ml` rows created new (factor 1); existing `piece` row flagged.
- `app/Enums/BaseUnit.php` **deleted**.
- `products.base_unit` stays a string, validated against is-base names; `Product::baseUnit()` returns `?string`.

## 4. Background: what the enum does today (all four jobs move to DB)

1. Allowed-base list — `Rule::enum(BaseUnit::class)` in Units Create/Edit + ProductForm → becomes `Rule::in(Unit::where('is_base', true)->pluck('name'))`.
2. Purchase/entry-unit mapping — `Gram→kg`, `Piece→piece`, `Millilitre→litre` → becomes base row's `purchase_unit`.
3. Integer-vs-decimal — `piece` whole-only → becomes base row's `integer_only`.
4. Display mapping — `displayStock()` `kg`-branch → generalized `"{qty} {purchase} ({base} {baseName})"`.

## 5. Implementation areas

### 5.1 Database (additive, live-safe)

1. `add_base_config_to_units_table` (DDL):
   ```php
   Schema::table('units', function (Blueprint $table) {
       $table->boolean('is_base')->default(false)->after('base_unit');
       $table->string('purchase_unit')->nullable()->after('is_base');
       $table->boolean('integer_only')->default(false)->after('purchase_unit');
   });
   ```
   Down: `dropColumn(['is_base', 'purchase_unit', 'integer_only'])`.
2. `seed_base_unit_rows` (DML, separate file, idempotent): flag `piece` (`is_base true, purchase_unit 'piece', integer_only true`); insert `g` (`purchase_unit 'kg'`, factor 1, `base_unit` null, sort_order next); insert `ml` (`purchase_unit 'litre'`, factor 1, `base_unit` null). Skip rows whose names already exist (except updating the flag/config on `piece`). Down: no-op with comment (data seed; forward-fix).
3. Update `database/seeders/UnitSeeder.php` (include `g`/`ml` base rows + flags) and `database/factories/UnitFactory.php` (sensible defaults: `base_unit` null-safe, `is_base false`, `integer_only false`).

### 5.2 Models

- `app/Models/Unit.php`: drop `BaseUnit` import + enum cast (`base_unit` plain string in casts or removed); add `scopeIsBase()`, `baseRow(string $base): ?Unit` helper (`where('base_unit', $base)->orWhere(...)`? No — base rows have `base_unit null`, so `where('name', $base)->where('is_base', true)`); keep `factorFor()` fallback map (add `litre`/`ml` arms already present? verify) and `toBaseQty()`.
- `app/Models/Product.php`: `baseUnit(): ?string` (column, derivation fallback when null — derivation now compares plain strings); `purchaseUnit(): string` from base row's `purchase_unit`, fallback to base name itself when row/column missing; `displayStock()` generic; `stockInUnit()` unchanged; update phpdoc (`BaseUnit|null` → `string|null`); casts `base_unit` plain (remove enum cast). `qtyRulesForUnit()` in components reads base row's `integer_only` instead of `$unit === 'piece'`.
- Delete `app/Enums/BaseUnit.php`.

### 5.3 Livewire + Blades

- `Units/Create.php`, `Units/Edit.php`: `base_unit` rule `Rule::enum` → `Rule::in(Unit::where('is_base', true)->pluck('name'))` (a base row itself uses `base_unit null` — allow nullable for is-base rows: rule becomes `nullable` + required-unless-is-base; simplest: `'base_unit' => ['nullable', 'string', Rule::in(all unit names incl. self)]`? Decide at implementation: base rows store `base_unit = null`, child rows must reference an existing is-base name OR any unit name? Keep: child `base_unit` must be an is-base name; is-base rows leave it null). Add `is_base` checkbox + `purchase_unit` + `integer_only` inputs (visible/required when is_base ticked); guards: cannot unflag/delete a base while child `units.base_unit` rows or `products.base_unit` reference it (friendly ValidationException, mirror purchase-delete guard style).
- `units/create.blade.php`, `units/edit.blade.php` (`:26-27`): base `<select>` options become dynamic loop over is-base rows + dev-note rewrite.
- `ProductForm.php`: `baseUnit` stays string property; rule → is-base names; `units()` computed `where('base_unit', baseUnit)` unchanged (works — child rows carry base names); `unitRows.*.unit` strict list unchanged in shape.
- `product-form.blade.php:55-56`: base `<select>` dynamic from is-base rows + dev-note rewrite.
- `Purchases/Create.php|Edit.php`, `Wastages/Create.php|Edit.php`: `rows.*.unit` lists from base rows' `purchase_unit` values (`Unit::where('is_base', true)->whereNotNull('purchase_unit')->pluck('purchase_unit')`); per-row piece-whole assertions → base row `integer_only` check; `formUnit`/`selectedProduct` computeds unchanged (string-based); 4 qty-hint lines derive from base row (purchase unit + integer flag) instead of `=== 'piece'` ternary.
- `Reports/Stock.php`, dashboard, prices, products blades: already string-driven via `displayStock()` — no change expected (verify at implementation).

### 5.4 Validation matrix (must all pass)

| Case | Result |
|---|---|
| Admin creates new base `meter` (purchase `cm`, decimal) purely via Units UI | usable in ProductForm → purchase → display with zero deploy |
| Unflag `g` while `1 kg.base_unit = g` | blocked, friendly error |
| Delete `piece` base row while products use it | blocked |
| Product with `base_unit` referencing deleted/renamed base | falls back (base name itself / derivation), never fatal |
| Existing g/piece flows | byte-identical behavior (kg entry, piece whole-only) |
| drop-unused-columns executed first; purchase/wastage writes contain no `product_unit_id` | is_base rules unaffected (zero column overlap — verified) |

### 5.5 Tests (Pest, sqlite `:memory:`, never delete without approval)

- Seed migration flags `piece`, creates `g`/`ml` with correct config (mirror existing seed-guard test style).
- `Rule::in` rejections for unknown bases; dynamic selects render is-base options.
- End-to-end UI-only new base (`meter`/`cm`): product → decimal purchase → converted display.
- Guard tests (unflag/delete blocked while referenced); fallback paths (null base row).
- Update existing `->value` enum accesses + factory states (`AdminTest`, `AdminInventoryTest`, `DecimalInventoryTest`, `ProductSearchTest`, `AdminSettingsTest`).
- Assert `BaseUnit` fully gone: grep in test (`expect(class_exists(...))->toBeFalse()` or file-absence assert).

### 5.6 Verification & rollout

- `vendor/bin/pint --dirty --format agent`, `php artisan migrate --force`, `php artisan test --compact`.
- No build expected (dynamic loops reuse existing classes; verify at implementation).
- Prod: upload PHP/Blade, `migrate --force` (DDL → seed, in order). Deploy alongside/after drop-unused-columns' push (single push + one `migrate --force` covers both; order between them doesn't matter functionally). Update shipped plan docs' enum references (`docs/*.md` + `.opencode/plans/*.md` note the shift; do not rewrite history sections, append records).

## 6. Risks / caveats

- Misconfiguration moves from code review to runtime (wrong purchase unit on a base row breaks conversions). Mitigations: reference guards, retained `factorFor()` code fallback, tests.
- `products.base_unit` strings referencing a renamed base go to fallback display; renames should be treated as delete+create (blocked while referenced).
- Low-stock `[1,10]` thresholds stay base-raw (separate follow-up, unchanged).

## 7. Out of scope

- Storefront/cart/checkout/order wiring to `current_stock`; `scopeAvailable`/`inStock()` changes.
- ml child/sellable units; per-unit `stock_quantity`; expiry/batch; CSV; low-stock alerts.
- `displayStock()` per-selling-unit breakdown (single-line decision stands).
- Squashing old migration pairs (covered by drop-unused-columns plan: `053624`/`065122` files deleted there, not here).

## 8. Execution checklist (for implementer, when approved)

1. DDL `add_base_config_to_units_table`.
2. DML `seed_base_unit_rows` + seeder/factory updates.
3. `Unit` model (cast drop, scopes/helpers) + `Product` model (string base, generic display/purchase-unit/integer paths).
4. Delete enum file.
5. Units Create/Edit (+blades) is-base/purchase-unit/integer inputs + guards + dynamic selects.
6. ProductForm (+blade) dynamic base select + is-base rules.
7. Purchases/Wastages rules + hints from base rows.
8. Dev-note rewrites (units create/edit, product-form).
9. Tests (Sec 5.5) + existing suite updates.
10. pint, migrate, full suite. Commit only when explicitly requested.
