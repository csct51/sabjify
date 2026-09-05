# Plan: products.base_unit + full base-unit admin functionality

Status: **Implemented** — executed after user approval.

## Implementation Record

- **Migrations shipped:** `2026_09_03_072146_add_base_unit_to_products_table` (DDL, nullable string), `072147_backfill_products_base_unit` (DML chunk backfill, default `g` — all 24 local products backfilled sensibly), `072148_repair_pre_seed_kg_stock` (DML repair of `base_qty == qty` kg rows + stock rebuild). All ran clean via `migrate --force`. Local corrupted rows had already been deleted by user via admin, so repair fixed 0 rows locally; covered by test fixture instead.
- **Models:** `Product` fillable/cast `base_unit`, column-first `baseUnit()` + derivation fallback, `stockInUnit()`; `Unit::factorFor()` code fallback + `Unit::toBaseQty()` seed-proof helper — all 8 purchase/wastage conversion sites rewired to it.
- **ProductForm:** persists `base_unit`, strict same-base `Rule::in` re-tightened; existing form tests updated to seed the `1 kg` unit row.
- **Purchases/Wastages:** enum-driven unit rules, new `show` pages + routes, index numbers link to show.
- **Displays:** products Qty, prices pill, dashboard Qty → `displayStock()`; Stock toggle column relabeled `Storefront` (behavior unchanged).
- **Tests:** new `AdminInventoryTest` (8: persistence, mixed-base rejection, legacy fallback, stockInUnit, missing-row fallback, both show pages, repair migration). Suite 363/363 ✓ (1222 assertions), pint clean ✓. No build (existing classes only).

## Follow-up: BaseUnit enum → DB-driven base units

- Superseded by `.opencode/plans/db-driven-base-units.md` (implemented): `units.is_base` + `purchase_unit` + `integer_only` columns replace the enum; `app/Enums/BaseUnit.php` deleted; `products.base_unit` now a plain string validated against is-base rows. The dev note "add a new case to BaseUnit enum" is rewritten wherever it appeared.

## Follow-up: purchase delete floors stock at zero (no more minus)

- **Rule:** deleting a purchase always succeeds and reverts `current_stock`, floored at `max(0, ...)` — both `Purchases/Index::delete()` and `Purchases/Edit::delete()` (previously Index blocked with an error, Edit went negative).
- **Implementation:** locked `lockForUpdate()` read + `update(['current_stock' => max(0, round(stock − revert, 3))])` inside the existing transactions; removed now-unused `Validator`/`ValidationException` imports from Index.
- **Trade-off (accepted):** clamp absorbs the difference — e.g. +10 kg in, −9 kg wasted, delete purchase → 0 even though 1 kg physically remains. Ledger exactness yields to convenience.
- **Tests:** list-delete floors at 0 after partial wastage, edit-delete floors at 0, full revert still exact. Suite 366/366 ✓ (1233 assertions), pint clean ✓.

## 1. Goal

- Add `products.base_unit` (`g`/`piece`) as the single source of truth — no more 3-hop derivation (`product_unit` → `units` row → base) in `Product::baseUnit()`.
- Guarantee the full loop: purchase/wastage entered in `kg`/`piece` → **stored in base** (`g`/`piece`) → **displayed converted per units** everywhere in admin.
- Finish admin gaps: strict one-product-one-base validation, converted stock displays, Purchases/Wastages Show pages, repair of pre-seed corrupted kg rows.
- Storefront untouched (`product_units.in_stock` + `Product::inStock()` as-is) — admin-only until user signals.

## 2. Standing rules (do NOT forget)

- **Phase 2 — Live Database Rules** (`AGENTS.md:184-190`): NEVER `migrate:fresh/refresh/reset`, `db:wipe`. Additive `Schema::table` migrations only + `php artisan migrate --force` on prod. New columns nullable/defaults; no history rewrite (orders/baskets/carts untouched).
- **Plan workflow:** plans live in `docs/` (no timestamp, just plan name). Finish plan file first, then **ask whether to execute; only execute on explicit yes**.
- **Developer note (permanent):** `To add a new base unit, add a new case to BaseUnit enum.` Lives in `app/Enums/BaseUnit.php` docblock + `units/create:30`, `units/edit:30`, `product-form:59`.
- **Key context (so last-prompt-only implementation does not recur):** base-unit storage in grams is already the shipped design (`purchase_items.base_qty = round(toBase(qty),3)`, `products.current_stock` incremented by it); the potato `5.5 g` incident was the missing-`kg`-seed bug (fixed via `2026_09_03_070419_seed_purchase_units`), not purchase-unit storage. `BaseUnit` enum `g`/`piece` + `purchaseUnit()` (`kg` for `g`) is source of truth for qty branching. Piece stays integer-only; `rate`/`line_total` stay integer (₹).

## 3. Locked decisions (user-confirmed)

- **Stock display:** converted single-line everywhere — `2.5 kg (2500 g)` / `7 piece` via existing `Product::displayStock()`.
- **Reports/Stock toggle:** keep as manual storefront override (`units.in_stock`), relabeled so it is not confused with qty.
- **Repair:** yes — DML migration recomputes pre-seed corrupted kg rows and rebuilds affected `current_stock`.

## 4. Current state (verified)

- `Product::baseUnit()` (`app/Models/Product.php:97-109`) derives from first unit name → `Unit` lookup, with name-sniffing fallback; `purchaseUnit()` (`:111-130`) maps to `kg`/`piece`.
- `ProductForm.baseUnit` (`app/Livewire/Admin/ProductForm.php:42`) filters the units dropdown (`:130-134`) but is **never persisted**; `unitRows.*.unit` is loose `string|max:20` (`:159`).
- Displays still raw in places: `products.blade.php:67-71` Qty pill `{{ current_stock }}`, `prices.blade.php:95-96` per-row pill, `dashboard.blade.php:71` `Qty:`.
- `Reports/Stock.php:51-57` `toggleStock()` flips `units.in_stock`; summary/filters already `current_stock`-based.
- Routes (`routes/web.php:122-128`): purchases/wastages have index/create/edit only — no show.
- `units.name` is unique (`2026_08_05_095300:16`); items reference units by name string (no FK) — deleting/adding unit rows is stock-math safe.

## 5. Implementation areas

### 5.1 Database (3 new migrations, all additive)

1. `add_base_unit_to_products_table` (DDL):
   ```php
   Schema::table('products', function (Blueprint $table) {
       $table->string('base_unit')->nullable()->after('unit');
   });
   ```
   Down: `dropColumn('base_unit')`. String (not MySQL enum) so adding `ml` later needs no enum alter; PHP `BaseUnit` enum + cast stays source of truth.
2. `backfill_products_base_unit` (DML, separate file): PHP `chunkById` loop over products, compute today's derivation per product, default `'g'` when unresolvable; `update(['base_unit' => ...])`. Down: no-op with comment (backfill irreversible by design; forward-fix instead).
3. `repair_pre_seed_kg_stock` (DML, user-approved): for `purchase_items`/`wastage_items` with `unit = 'kg'` and `base_qty == qty` (corruption fingerprint — a correctly converted row never has `base_qty == qty` for kg since factor is 1000): set `base_qty = round(qty * 1000, 3)`; then for each affected product rebuild `current_stock = Σ purchase base_qty − Σ wastage base_qty`. Log affected counts via `$this->command->info()` when running in console. Down: no-op with comment.

### 5.2 Models

- `app/Models/Product.php`: add `'base_unit'` to `#[Fillable]`, `'base_unit' => BaseUnit::class` to `casts()`, `@property` phpdoc. Rewrite `baseUnit()`: return `$this->base_unit` (enum cast) when set, else fall back to current derivation (legacy safety net). `purchaseUnit()`/`displayStock()` unchanged.
- New `Product::stockInUnit(string $unitName): float`: resolve `Unit` by name → `fromBase((float) current_stock)` rounded 3; **code fallback** when row missing (`kg`→÷1000, `piece`→identity) so a missing seed can never again silently corrupt or misdisplay. Used by prices rows; purchases/wastage math keeps using `Unit::toBase` (rows now seeded) with this fallback as safety net — implement as small private helper shared via `Unit` model (e.g. `Unit::factorFor(string $name): ?float` returning row factor or fallback map) to avoid duplicating the map.

### 5.3 ProductForm — enforce one product = one base

- `mount`: hydrate `baseUnit` from `$product->base_unit?->value` (fall back to derivation, then `'g'`).
- `save`: validate `baseUnit` (`Rule::enum`, exists) + re-tighten `unitRows.*.unit` to `Rule::in(Unit::where('base_unit', $this->baseUnit)->pluck('name'))` + explicit after-check rejecting rows whose looked-up base differs (covers units created after page load); persist `'base_unit' => $this->baseUnit` in `$data` for create + update.
- Keep `unitRows.*.in_stock` checkboxes (storefront booleans, untouched semantics).

### 5.4 Purchases / Wastages — no math change, finish CRUD

- Replace hardcoded `'in:piece,kg'` messages/rules with `Rule::in(array_keys(...))` derived from `BaseUnit::options()` + `purchaseUnit()` values where trivially safe; keep per-row piece whole-number assertion. No change to decimal math, totals, revert paths.
- Add read-only Show pages mirroring `Suppliers/Show` pattern: `Purchases/Show.php` + `purchases/show.blade.php` (`admin.purchases.show`), `Wastages/Show.php` + `wastages/show.blade.php` (`admin.wastages.show`): header (number/date/supplier-or-reason/remark/total), item table (product, unit, rate, qty, line_total/base_qty), derived stock effect note; link from Index rows (purchase number → show; keep Edit action).

### 5.5 Admin displays — converted single-line

- `products.blade.php:67-71`: Qty pill content → `{{ $product->displayStock() }}` (keep sky/stone colors + `> 0` condition on `(float) current_stock`).
- `prices.blade.php:95-96`: pill → `{{ $unit->product?->displayStock() ?? '—' }}` (product-level converted stock; per-row pack counts stay out per locked decision).
- `dashboard.blade.php:71`: `Qty: {{ $product->current_stock }}` → `Qty: {{ $product->displayStock() }}`.
- Stock report: already converted; relabel toggle column header + aria-label to `Storefront visibility` (behavior unchanged — manual `in_stock` override).
- Reuse existing Tailwind classes only → no `npm run build` needed.

### 5.6 Validation matrix (must all pass)

| Case | Result |
|---|---|
| Product create `baseUnit g` + rows `1 kg/500 g` | OK, `products.base_unit = g` |
| Rows mixing `1 kg` + `1 pc` | Rejected (shared-base check) |
| Legacy product `base_unit null` | `baseUnit()` derives as before |
| Purchase `0.5` kg (seeded `kg` row) | `base_qty 500`, stock `+500` |
| `Unit 'kg'` row deleted (fallback) | conversion still ×1000 via code map |
| Repair migration on fixture (`base_qty == qty` kg rows) | `base_qty` ×1000, stock rebuilt |
| Show pages | render number/items/totals |

### 5.7 Tests (Pest, sqlite `:memory:`, in existing/new files — never delete without approval)

- `ProductForm` persists `base_unit`; mixed-base rows rejected; null-base legacy fallback.
- `stockInUnit` math incl. missing-row fallback; `displayStock` formats.
- Show pages render for purchase + wastage fixtures.
- Repair migration logic covered via a dedicated test that seeds corrupted rows and runs the repair class logic (or asserts end state after `migrate` — prefer direct logic test to avoid re-running migrations).
- Keep `DecimalInventoryTest` seed guard green; full suite must pass.

### 5.8 Verification & rollout

- `vendor/bin/pint --dirty --format agent`, `php artisan migrate --force`, `php artisan test --compact`.
- Prod deploy: upload changed PHP/Blade (+ `public/build/` only if build ran — not needed), `php artisan migrate --force` (runs DDL → backfill → repair → seed in order). Never `fresh/refresh/reset/wipe`.

## 6. Risks / caveats

- Repair migration rewrites `current_stock` for affected products — user-approved, but orders history untouched (order items snapshot unit/price; storefront reads `in_stock`, unaffected).
- `base_unit` column + `units.base_unit` can theoretically drift; ProductForm filtering + shared-base validation prevents it through UI. Direct DB edits are out of scope.
- `->change()` not used here (all adds/inserts) — no dbal/MySQL-modify risk.
- Low-stock `[1,10]` thresholds stay base-raw (follow-up, not this plan).

## 7. Out of scope

- Storefront/cart/checkout/order wiring to `current_stock`; `scopeAvailable`/`inStock()` changes.
- New base units (`ml`); per-unit `stock_quantity` revival; expiry/batch; CSV; low-stock alerts.
- Squashing `053624`+`065122` (already ran locally; prod state to be confirmed at deploy).

## 8. Execution checklist (for implementer, when approved)

1. DDL `add_base_unit_to_products_table`.
2. DML `backfill_products_base_unit`.
3. DML `repair_pre_seed_kg_stock`.
4. `Product` cast/fillable/`baseUnit()`/`stockInUnit()` + `Unit::factorFor()` fallback.
5. `ProductForm` persist + strict validation.
6. Purchases/Wastages enum-driven unit rules + Show pages + routes + index links.
7. Blade display swaps + toggle relabel.
8. Tests (Sec 5.7).
9. pint, migrate, full suite. Commit only when explicitly requested.
