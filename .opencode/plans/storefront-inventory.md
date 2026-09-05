# Plan: storefront inventory wiring + selling report

Status: **Implemented** — executed after user approval.

## Implementation Record

- **Schema:** `2026_09_05_060819_add_base_qty_to_order_items_table` (nullable decimal, snapshotted at placement). Ran clean via `migrate --force`.
- **Models:** `OrderItem` fillable/cast + `soldBaseQty()` (snapshot, live-convert fallback for pre-wiring rows); `Product::sellablePacksFor()`, `baseNeededFor()`, `lowPacksThreshold()` (converted `low_stock` or 5).
- **OrderService:** `createFromCart` pre-check + atomic locked re-check, decrement, `base_qty` snapshot (baskets skipped); `cancel` restores via snapshot (both customer + admin paths). Pre-wiring orders untouched.
- **Storefront:** picker stepper cap + Only-left pill + add block; Detail/Card add+increment blocks; `Cart::increment` cap; Cart/Checkout `outOfStockItems` toggle OR qty (Checkout also made null-safe). Toggle semantics unchanged.
- **Report:** `Reports/Selling` + blade + `admin.reports.selling` route + sidebar (banknote icon — chart icon not registered); per-product packs/base/revenue, header totals, date presets + custom range, category, search, non-cancelled scope; AJAX pager (plain links() would full-reload and reset filters).
- **Tests:** new `StorefrontInventoryTest` (8: picker block/cap, cart cap, service refuse, placement decrement + cancel restore, toggle-off gate, report math/scope/date filter). Suite 389/389 ✓ (1324 assertions), pint clean ✓. Fixes mid-flight: test `(float)` casts, `created_at` via direct assignment (fillable-blocked), AJAX pager, registered icon. No build (existing classes only).

## 1. Goal (user-locked)

Wire `current_stock` into the storefront while keeping the `in_stock` toggle as master kill-switch. Add a selling report. Baskets excluded throughout (no constituent tracking).

## 2. Standing rules (do NOT forget)

- **Phase 2 — Live Database Rules** (`AGENTS.md:184-190`): NEVER `migrate:fresh/refresh/reset`, `db:wipe`. Additive migrations only + `migrate --force` on prod.
- **Push state:** nothing uploaded to production since `deaec59` — all Phase 2 migrations unpushed.
- Related plans: `db-driven-base-units` + `drop-unused-columns` + `low-stock-alerts` (all implemented).
- Toggle semantics unchanged: OFF hides/disables everything exactly as today.

## 3. Locked decisions (user-confirmed)

- **Decrement at placement** (`createFromCart`), restore on `cancel` (both customer + admin paths funnel through `OrderService::cancel`).
- **Report scope:** all non-cancelled orders, products only (basket rows skipped).
- **UX:** block + "Only X left" (pill when sellable packs ≤ converted `low_stock` threshold where set, else ≤ 5).

## 4. Implementation areas

### 4.1 Schema (additive, nullable)

- `add_base_qty_to_order_items_table`: `$table->decimal('base_qty', 10, 3)->nullable()->after('quantity')`. Down: drop column. Snapshot principle (exact history even if factors change).

### 4.2 Helpers (`Product`)

- `sellablePacksFor(?ProductUnit $unit): int` — `floor(stock / factor(unit name))`, min 0. Toggle checked by callers (unchanged).
- `baseNeededFor(?ProductUnit $unit, int $packs): float` — packs × factor, rounded 3.
- Both via `Unit::factorFor()` (seed-proof). `lowPacksThreshold()`: `low_stock` converted to packs when set, else 5.

### 4.3 OrderService (atomic, locked)

- `createFromCart`: existing toggle check stays; add per-line base-need check; inside transaction `lockForUpdate` each product, re-check, `decrement`, snapshot `base_qty` on the item. Basket rows skipped. Keep exception shape (`RuntimeException` listing names).
- `cancel`: after status flip, `increment` each product item's `base_qty` (fallback live-convert for pre-wiring null rows). Pre-wiring orders untouched elsewhere.

### 4.4 Storefront enforcement

- `ProductUnitPicker`: stepper caps at sellable packs; "Only X left" pill; `addToCart` blocks when need > stock.
- `ProductDetail` / `ProductCard::ensureStock`: keep toggle gate; add qty gate for requested qty.
- `Cart::increment`: cap at sellable packs; `outOfStockItems`: toggle OR need(cart qty) > stock.
- `Checkout`: pre-place check mirrors OrderService (toggle + qty); service re-checks atomically.

### 4.5 Selling report (`admin.reports.selling`, Reports group + sidebar)

- `Reports/Selling.php` + `reports/selling.blade.php`: per-product rows (packs = sum quantity, base = sum `base_qty` w/ live-convert fallback for null rows, revenue = sum total), header totals (revenue, orders, packs), filters (today/7d/30d/custom via date inputs, category, search), scope `status != cancelled`. Paginate 20. No CSV v1.

### 4.6 Tests (Pest, sqlite memory)

Oversell blocked at picker/cart/checkout/service; placement decrements exact base; cancel restores; pre-wiring null `base_qty` falls back; report math + filters + cancelled excluded; toggle OFF gates everything; suite + pint.

## 5. Verification & rollout

`vendor/bin/pint --dirty --format agent`, `php artisan migrate --force`, `php artisan test --compact`. No build unless new classes (verify). Prod: upload + `migrate --force`. Commit only when explicitly requested.

## 6. Out of scope

Basket constituent deduction, CSV export, retro-adjusting pre-wiring orders, toggle semantics changes, storefront low-stock config UI.
