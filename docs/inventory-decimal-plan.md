# Plan: Decimal purchase / wastage qty in product base unit (kg or piece)

Status: **Implemented** — executed 2026-09-03 after user approval.

## Implementation Record

- **Migrations shipped:** `2026_09_03_054728_alter_products_current_stock_decimal`, `054729_alter_purchase_items_qty_decimal`, `054730_alter_wastage_items_qty_decimal`, `054731_alter_wastages_total_qty_decimal` — all `Schema::table ... ->change()` to `decimal(10/12,3) default 0`, ran clean on local MySQL via `php artisan migrate --force`.
- **Bug fixed along the way:** `Purchases/Edit.php delete()` reverted `(int)qty` instead of `base_qty` — now float `base_qty`-first like save-revert.
- **Tests:** new `tests/Feature/DecimalInventoryTest.php` (4 tests: decimal kg purchase, fractional piece rejected, decimal wastage, over-stock rejected). Factory note: `ProductFactory::configure()` overwrites `current_stock` with random 10-100, so tests set stock via `$product->update()` after create.
- **Verification at ship:** `vendor/bin/pint --dirty` fixed `Product.php` style ✓ · `npm run build` 4.68s ✓ · `php artisan test --compact` → 352/352 ✓ (1186 assertions, baseline was 348).

## Follow-up fix: missing `kg`/`piece` unit rows (wrong Available value)

- **Symptom:** `Available: 0.006 kg (5.5 g)` for potato — a 5.5 kg purchase stored `base_qty = 5.5` (grams) instead of `5500`.
- **Root cause:** `UnitSeeder` never created `kg`/`piece` rows and migration `053633` only `UPDATE`d existing rows, so `Unit::where('name', 'kg')->first()` was null and purchase/wastage code fell back to unconverted qty. Piece was harmless (factor 1); kg missed ×1000.
- **Fix:** data-only migration `2026_09_03_070419_seed_purchase_units.php` (idempotent insert of `kg` g/1000 + `piece` piece/1, reversible) + `UnitSeeder` extended with both rows. `DecimalInventoryTest` beforeEach no longer creates `kg`/`piece` so the suite guards the seed, plus a dedicated seed assertion test.
- **Verification:** full suite 355/355 ✓ (1201 assertions), pint clean ✓.
- **Known corrupted data (NOT auto-repaired):** kg purchase/wastage rows created before this fix have `base_qty == qty` (e.g. potato `current_stock 5.5`, purchase_items #5/#6). Needs a repair migration (recompute `base_qty = qty × factor`, rebuild `current_stock`) or manual correction — ask user before rewriting live stock.

## 1. Goal

How the user wants it to be (verbatim requirement, persisted so it is not forgotten 2-3 prompts later):

- We purchase in **kg or piece — whatever the product's base unit is**.
- Admin can also buy like **500g by entering decimal quantity** (e.g. `0.5` kg).
- We **reflect stock with decimal values as well**.
- Same applies for **wastage**.

Meaning:

- `g`-base product → purchase/wastage unit is `kg`, qty accepts decimals (`0.5` kg = `500` g base).
- `piece`-base product → purchase/wastage unit is `piece`, qty stays whole numbers only.
- `products.current_stock` (stored in **base unit**: `g` / `piece`) becomes decimal-aware so fractional base amounts persist (e.g. `555.5` g).

## 2. Standing rules (do NOT forget — user flagged forgetting after 2-3 prompts)

- **Phase 2 — Live Database Rules** (`AGENTS.md:184-190`):
  - App is LIVE (Phase 1) receiving orders. NEVER run `migrate:fresh` / `refresh` / `reset` / `db:wipe`.
  - All schema changes are additive `Schema::table` migrations only: `php artisan make:migration ...` + `php artisan migrate --force` on production.
  - New/altered columns must be `nullable` or have safe defaults; no history rewrite.
- **Plan workflow agreed with user:**
  - Plans live in `docs/` folder like before (e.g. `docs/per-unit-stock-plan.md`), **no timestamp, just plan name**. This file is `docs/inventory-decimal-plan.md`.
  - Plan is made in build mode (plan mode cannot write md files).
  - Once plan file is finished, **ask user whether to execute plan or not. Only execute when user says so. Ask proactively.**
- **Developer note (permanent):**
  - Text: `To add a new base unit, add a new case to BaseUnit enum.`
  - Currently only in 3 Blades, missing in enum itself:
    - `resources/views/livewire/admin/units/create.blade.php:30`
    - `resources/views/livewire/admin/units/edit.blade.php:30` (assumed same, verify)
    - `resources/views/livewire/admin/product-form.blade.php:59`
  - This plan adds it as docblock in `app/Enums/BaseUnit.php:5` (single source of truth) and keeps the 3 Blade hints.
- **Scope memory (full admin context, so last-prompt-only implementation does not recur):**
  - Units are strict `g` / `piece` via `App\Enums\BaseUnit` (`app/Enums/BaseUnit.php:5-8`), `Unit.base_unit` cast (`app/Models/Unit.php:48-53`), `to_base_factor decimal(10,4)` + `toBase()/fromBase()` float-aware (`app/Models/Unit.php:56-66`).
  - One product = one base; `ProductForm.baseUnit` filters `Unit::where('base_unit', baseUnit)` (`app/Livewire/Admin/ProductForm.php:130-134`), `Product::baseUnit()` / `purchaseUnit()` derive `kg` for `g` else `piece` (`app/Models/Product.php:97-130`).
  - Purchases foldered `Purchases/Index|Create|Edit` (`PUR-`), Suppliers, Wastages `WST-`, `Reports/Stock` per-product `current_stock`, Dashboard out-of-stock — all **admin-only** until user signals storefront.
  - Storefront still uses `product_units.in_stock` boolean (`Product::inStock()` `app/Models/Product.php:132-137`, `scopeAvailable()`). This plan does **not** rewire storefront/orders to `current_stock`.

## 3. Current state (verified)

### DB — all integer, blocks `0.5`

- `database/migrations/2026_09_02_065121_add_current_stock_to_products_table.php:15` — `products.current_stock unsignedInteger default 0`.
- `database/migrations/2026_09_02_053647_create_purchase_items_table.php:21,23` — `purchase_items.qty unsignedInteger`, `base_qty unsignedInteger default 0` (`rate`/`line_total` integer = money, OK).
- `database/migrations/2026_09_02_112334_create_wastage_items_table.php:20-21` — `wastage_items.qty/base_qty unsignedInteger`.
- `database/migrations/2026_09_02_112333_create_wastages_table.php:20` — `wastages.total_qty unsignedInteger default 0`.
- `database/migrations/2026_09_02_053633_add_conversion_to_units_table.php:16-17` — `units.to_base_factor decimal(10,4)` already decimal. Good.

### Models — casts integer

- `app/Models/Product.php:31,54` — `@property int $current_stock`, cast `'current_stock' => 'integer'`.
- `app/Models/PurchaseItem.php:30,32` — `'qty' => 'integer'`, `'base_qty' => 'integer'`.
- `app/Models/WastageItem.php:27-28` — `'qty' => 'integer'`, `'base_qty' => 'integer'`.
- `app/Models/Wastage.php:29` — `'total_qty' => 'integer'`.
- `app/Models/Unit.php:52` — `'to_base_factor' => 'decimal:4'`, `toBase(float): float` / `fromBase(float): float` already float-aware; callers truncate with `(int)`.

### Livewire — `integer|min:1` + `(int)` truncation

- `app/Livewire/Admin/Purchases/Create.php:74-77` — `formRate integer|min:1`, `formQty integer|min:1`; `:136-138` — `rows.*.unit in:piece,kg`, `rows.*.rate integer`, `rows.*.qty integer|min:1`.
- `app/Livewire/Admin/Purchases/Create.php:147,163,165,173-174,178` — `(int)rate * (int)qty`, `(int)toBase((float)qty)`, `(int)qty` persist, `increment('current_stock', $baseQty)` int.
- `app/Livewire/Admin/Purchases/Create.php:187-202` — dead `calculateBaseQty(int): int` with `ceil`.
- `app/Livewire/Admin/Purchases/Edit.php:92-93,175-176` — same integer rules; `:187,214,216,224` — `(int)` sums; `:143` **bug**: `delete()` uses `(int)oldItem->qty` instead of `base_qty` (save-revert at `:193` correctly uses `base_qty`); `:238-253` — same dead `calculateBaseQty`.
- `app/Livewire/Admin/Purchases/Index.php:35,42` — `(int)base_qty / (int)qty` revert on delete.
- `app/Livewire/Admin/Wastages/Create.php:76,126` — `formQty integer|min:1`, `rows.*.qty integer|min:1`, `rows.*.unit in:piece,kg`; `:93,136,143,162,169` — `(int)toBase`, `(int)` sums/persist; `:173` — `decrement` int.
- `app/Livewire/Admin/Wastages/Edit.php:89,159` — same integer rules; `:104,142-143,170,176,185,201,208-209` — `(int)` casts; `:131-151` delete restores via `base_qty` (correct pattern).
- `app/Livewire/Admin/Reports/Stock.php:64-66,80-82` — summary/filters `current_stock > 0 / = 0 / [1,10]` integer thresholds; `:52-56` `toggleStock()` toggles `product_units.in_stock`, not `current_stock` (known split, out of scope for this plan except display).

### Blade — `step="1"` blocks decimal

- `resources/views/livewire/admin/purchases/create.blade.php:56,62` — `formRate min="1" step="1"`, `formQty min="1" step="1"`; `:68-69` preview `(int)rate * (int)qty`; `:101-103,136-138` row/grand totals `(int)` casts.
- `resources/views/livewire/admin/wastages/create.blade.php:68` — `formQty min="1" step="1"`.
- `resources/views/livewire/admin/purchases/edit.blade.php`, `wastages/edit.blade.php` — assumed same pattern, verify during execution.
- `resources/views/livewire/admin/reports/stock.blade.php:75-77,80-86` — raw `{{ $product->current_stock }}`, badges `> 10 / > 0 / = 0`, `Low ({{ current_stock }})`. No `fromBase` conversion.

## 4. Decisions (locked for execution)

- **Purchase/wastage unit = product's `purchaseUnit()`** (`kg` for `g`-base, `piece` for `piece`-base). No per-row unit switching; unit is derived at `addProduct()` and stored read-only per row.
- **Qty type branches on base:**
  - `g` (unit `kg`): `numeric|min:0.001|max:999999`, up to 3 decimals. `0.5` kg = `500` g.
  - `piece`: `integer|min:1` (unchanged). `0.5` piece rejected.
- **Stock stored in base unit as decimal:** `g` stocks like `500`, `555.5`; `piece` stocks like `10` (with `.000` scale but validated whole).
- **Precision:** `qty decimal(10,3)`, `base_qty decimal(12,3)`, `current_stock decimal(12,3) default 0`, `wastages.total_qty decimal(12,3) default 0`. `rate`/`line_total`/`total_amount` stay integer (money in ₹).
- **Math:** `baseQty = round(Unit::toBase((float)qty), 3)`; no `ceil`, no `(int)` on qty paths. `lineTotal = round((float)rate * (float)qty)` then cast to int for money? No — rate is per purchase-unit (₹/kg or ₹/piece), so `0.5` kg × ₹100 = ₹50. Keep `line_total` integer via `round(rate * qty)`; `total_amount` = sum of rounded line totals.
- **Wastage `total_qty`** = sum of `qty` (purchase-unit decimals, e.g. `1.25` kg), not `base_qty`, to match current semantics; display with unit context per row.
- **Stock display:** raw base value with `number_format` (trim trailing zeros); `g` rows additionally show `≈ X.XXX kg` via `/1000` (display only, no schema change). Low threshold stays `[1,10]` base units for now unless user asks to make it base-aware (flagged as follow-up, not in this plan).
- **Dev note:** add docblock to `BaseUnit.php`, keep Blade hints.

## 5. Implementation areas

### 5.1 Database (additive, live-safe)

Create via `php artisan make:migration ...` (4 files, all `Schema::table`, `default 0`, never `fresh`):

1. `alter_products_current_stock_decimal`:
   ```php
   Schema::table('products', function (Blueprint $table) {
       $table->decimal('current_stock', 12, 3)->default(0)->change();
   });
   ```
   Down: `->unsignedInteger('current_stock')->default(0)->change()`. Note: `change()` on MySQL requires no new package on Laravel 11+; if prod driver rejects, fallback is add temp column + copy + drop/rename in a second release (do not do in this plan without asking).
2. `alter_purchase_items_qty_decimal`:
   ```php
   Schema::table('purchase_items', function (Blueprint $table) {
       $table->decimal('qty', 10, 3)->default(0)->change();
       $table->decimal('base_qty', 12, 3)->default(0)->change();
   });
   ```
3. `alter_wastage_items_qty_decimal`: same for `wastage_items.qty (10,3)`, `base_qty (12,3)`.
4. `alter_wastages_total_qty_decimal`:
   ```php
   Schema::table('wastages', function (Blueprint $table) {
       $table->decimal('total_qty', 12, 3)->default(0)->change();
   });
   ```
   Run locally `php artisan migrate --force`; prod same command after code deploy (order: code tolerant of both int/decimal first — casts read both — then migrate).

### 5.2 Models (casts + phpdoc)

- `app/Models/Product.php:31,54` — `@property numeric $current_stock`, `'current_stock' => 'decimal:3'`.
- `app/Models/PurchaseItem.php:30,32` — `'qty' => 'decimal:3'`, `'base_qty' => 'decimal:3'`.
- `app/Models/WastageItem.php:27-28` — same `decimal:3`.
- `app/Models/Wastage.php:29` — `'total_qty' => 'decimal:3'`.
- `app/Models/Unit.php` — no change (already `decimal:4` + float methods).
- `app/Enums/BaseUnit.php:5` — add permanent dev note:
  ```php
  /**
   * To add a new base unit, add a new case to BaseUnit enum.
   *
   * DB transfer later (Phase 2, additive): Schema::table('units') alter base_unit enum,
   * then add Unit rows with to_base_factor relative to the new base.
   */
  ```

### 5.3 Livewire — Purchases Create/Edit

Files: `app/Livewire/Admin/Purchases/Create.php`, `app/Livewire/Admin/Purchases/Edit.php`, `app/Livewire/Admin/Purchases/Index.php`.

- Helper (add to both Create/Edit, or a shared trait if sibling pattern exists — check before creating new base folder per conventions):
  ```php
  private function qtyRulesForUnit(string $unit): array
  {
      return $unit === 'piece'
          ? ['required', 'integer', 'min:1']
          : ['required', 'numeric', 'min:0.001', 'max:999999'];
  }
  ```
- `addProduct()`:
  - Validate `formProductId` + `formRate integer|min:1` first, load `$product`, derive `$unit = $product->purchaseUnit()`.
  - Then validate `formQty` with `qtyRulesForUnit($unit)` + messages: `Qty must be a whole number` only for piece; `Qty must be at least 0.001` / `Enter e.g. 0.5 for 500g` for kg.
  - Push row `['product_id','unit' => $unit,'rate' => formRate,'qty' => (string)(float)formQty]`.
- `save()`:
  - Keep `rows.*.product_id / unit in:piece,kg / rate integer` as-is (do not broaden unit enum in this plan).
  - Replace `rows.*.qty integer|min:1` with `numeric|min:0.001`; then after base validation, loop rows, load product, assert per-row: if `unit === 'piece'` then `qty` must be whole (`floor((float)qty) == (float)qty`, else `ValidationException: Qty for piece must be whole`).
  - Math: `$qtyF = (float)$row['qty']; $baseQty = round($unitModel ? $unitModel->toBase($qtyF) : $qtyF, 3); $lineTotal = (int) round((float)$row['rate'] * $qtyF); $total = sum(lineTotals)`.
  - Persist `qty => $qtyF`, `base_qty => $baseQty` (no `(int)`), `increment('current_stock', $baseQty)` float.
  - Revert path (Edit save + Index/Edit delete): use `$oldItem->base_qty > 0 ? (float)$oldItem->base_qty : (float)$oldItem->qty`; float comparison `$product->current_stock < $decrementQty - 1e-9` guard.
  - Fix `Purchases/Edit.php:143` bug: `decrement('current_stock', (float)$oldItem->base_qty ?: (float)$oldItem->qty)` instead of `(int)qty`.
  - Remove or leave dead `calculateBaseQty()` untouched (out of scope; do not delete without approval per test rules — flag only).

### 5.4 Livewire — Wastages Create/Edit

Files: `app/Livewire/Admin/Wastages/Create.php`, `app/Livewire/Admin/Wastages/Edit.php`, `app/Livewire/Admin/Wastages/Index.php`.

- Same `qtyRulesForUnit()` branching as purchases.
- `addProduct()`: derive `$unit = $product->purchaseUnit()`, validate qty per unit, compute `$baseQty = round(toBase((float)formQty), 3)`, insufficient-stock check on `$baseQty`, push row.
- `save()`:
  - `rows.*.qty numeric|min:0.001` + per-row piece whole-number assertion (same as purchases).
  - `$totalQty = round(sum((float)qty), 3)` (purchase-unit sum).
  - Pre-check all rows with `lockForUpdate()` + float compare before `Wastage::create`, then per-row `baseQty = round(toBase, 3)`, persist floats, `decrement('current_stock', $baseQty)`.
  - Revert path (Edit save/delete): `increment` with `(float)base_qty ?: (float)qty`.
- `Wastages/Index.php` delete-restore already `base_qty`-first; change `(int)` to `(float)` + round.

### 5.5 Blade — inputs, totals, hints

- `resources/views/livewire/admin/purchases/create.blade.php:56,62` + `edit.blade.php` (mirror) + `wastages/create.blade.php:68` + `wastages/edit.blade.php` (mirror):
  - Rate stays `min="1" step="1"`.
  - Qty: `min="0.001" step="0.001"` + dynamic hint under input: if selected product purchaseUnit is `kg` show `Enter kg — e.g. 0.5 = 500g`; if `piece` show `Whole pieces only`. Implement via existing `formProductId` lookup or a small `#[Computed] formUnit` (derive without extra query if already loaded).
  - Live Total preview: `(float)rate * (float)qty` with `Number::currency(round(...))`; condition `(float)rate > 0 && (float)qty >= 0.001`.
  - Row lists: show `qty` raw (preserves `0.5`), unit label, line total `round(rate*qty)`; grand total sums rounded line totals. Replace all `(int)` casts in these 4 blades with `(float)` + `round`.
- `resources/views/livewire/admin/purchases/index.blade.php`, `wastages/index.blade.php` — qty display: no change except ensure decimals render (no `(int)` formatting; verify during execution).
- `resources/views/livewire/admin/reports/stock.blade.php:75-77,80-86`:
  - Replace `{{ $product->current_stock }}` with trimmed decimal: `{{ rtrim(rtrim(number_format((float)$product->current_stock, 3, '.', ''), '0'), '.') }}`.
  - For `g`-base products (check `$product->purchaseUnit() === 'kg'` or `baseUnit()?->value === 'g'`), append `≈ {{ number_format((float)$product->current_stock/1000, 3) }} kg` muted.
  - Keep badge thresholds `> 10 / > 0 / = 0` unchanged in this plan.
- Keep dev-note paragraphs in `units/create:30`, `units/edit:30`, `product-form:59` as-is.

### 5.6 Validation matrix (must all pass)

| Product base | Unit stored | Input | Result |
|---|---|---|---|
| `g` (e.g. Tomato) | `kg` | `0.5` | OK → `qty 0.5`, `base_qty 500.000`, stock `+500.000` |
| `g` | `kg` | `1.25` | OK → `base 1250.000` |
| `g` | `kg` | `0.000` / `-1` / `abc` | Rejected `min/numeric` |
| `piece` (e.g. Coconut) | `piece` | `2` | OK → `qty 2`, `base 2.000` |
| `piece` | `piece` | `0.5` / `1.5` | Rejected `must be whole` |
| Wastage `g` | `kg` | `0.25` with stock `1000` | OK → stock `999.750` (if prior `1000.000`) |
| Wastage any | — | `baseQty > current_stock` | Rejected `Insufficient stock` (float compare) |
| Purchase edit delete | — | revert `base_qty 500.5` | `decrement` exact float, no truncation |

### 5.7 Tests (Pest, `DB_CONNECTION=sqlite :memory:`)

- Update existing: any test asserting `integer` qty message or `(int)` totals for purchases/wastages (search `tests/Feature/*Purchase*`, `*Wastage*`, `*Stock*`, `AdminTest`) to new messages; factories: `PurchaseItemFactory`, `WastageItemFactory`, `ProductFactory current_stock` may need decimal states (check `database/factories/*` during execution).
- Add (in existing files, do not delete tests without approval):
  - Purchase `0.5` kg on `g` product → `purchase_items.qty 0.5`, `base_qty 500`, `products.current_stock` increased by `500` (float assert `assertEquals(500, (float)...)` or `assertDatabaseHas` with `500.000` depending on driver).
  - Purchase `0.5` piece rejected.
  - Wastage `0.25` kg decrements fractional stock; over-stock rejected.
  - Purchase edit revert uses `base_qty` float (regression for `:143` bug).
- Run minimum: `php artisan test --compact --filter=Purchase|Wastage|Stock|Admin` then full `php artisan test --compact` (baseline 348 passed / 1168 assertions before this plan).

### 5.8 Verification & rollout

- `vendor/bin/pint --dirty --format agent` after PHP changes.
- `npm run build` after Blade/Tailwind changes (new hint text uses existing utilities only, but build anyway per Tailwind rules).
- Local: `php artisan migrate --force`, tinker checks: `Product::create current_stock 0` → purchase `0.5` kg → `500.000`; wastage `0.25` → `499.750` equivalent; piece `1` → `1.000`, `0.5` rejected.
- Prod deploy: upload changed PHP/Blade + rebuilt `public/build/`; `php artisan migrate --force`; never `fresh/refresh/reset/wipe`.

## 6. Risks / caveats

- `->change()` to decimal on MySQL rewrites the table; catalog tables are small so fast, but deploy code first (tolerant via casts) then migrate to avoid `column type` window. If prod MySQL rejects `change()` without `doctrine/dbal`, stop and ask — fallback is a second additive migration with temp column (not in this plan).
- Float compare for stock guards uses epsilon (`1e-9`); display trims trailing zeros so `10.000` shows `10`.
- `calculateBaseQty()` dead code with `ceil` left untouched to minimize diff; it still returns int and is unused by new paths.
- Low-stock `[1,10]` thresholds are now base-unit raw (`10` = `10` g for `g` products = tiny). Kept intentionally; making it base-aware (e.g. `1000` g low) is a follow-up needing user decision.
- `toggleStock()` still toggles `product_units.in_stock`, not `current_stock`; storefront still `in_stock`-driven. No change in this plan per admin-only signal.

## 7. Out of scope

- Storefront/cart/checkout/order wiring to `current_stock`; `OrderService` stock decrement; `scopeAvailable` change.
- New base units (`ml`/litre); `BaseUnit` enum cases beyond `g`/`piece`.
- `purchases.show` / `wastages.show` detail views; audit history; CSV import/export; low-stock alerts.
- Squashing `053624 add_stock_quantity` + `065122 remove_stock_quantity` pair (asked separately, not part of decimal change).
- Per-unit `stock_quantity` revival; expiry/batch tracking; supplier purchase history linking.

## 8. Execution checklist (for implementer, when approved)

1. `php artisan make:migration alter_products_current_stock_decimal` + 3 qty migrations (Sec 5.1).
2. Models casts + `BaseUnit.php` docblock (Sec 5.2, 2).
3. Purchases Create/Edit/Index float math + per-unit qty rules + `:143` bug fix (Sec 5.3).
4. Wastages Create/Edit/Index float math + rules (Sec 5.4).
5. 4 form Blades `step/min`/hints/totals + stock display (Sec 5.5).
6. Factories/tests update + new decimal cases (Sec 5.7).
7. `vendor/bin/pint --dirty --format agent`, `npm run build`, `php artisan migrate --force`, `php artisan test --compact`.
8. Commit (only when explicitly requested) + prod deploy notes (Sec 5.8).
