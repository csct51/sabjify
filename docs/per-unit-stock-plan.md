# Per-Unit Stock Plan

Status: **Implemented** — released 2026-08-24.

## Implementation Record

- **Migration shipped:** `2026_08_24_080000_add_in_stock_to_product_units_table.php` — adds `product_units.in_stock` (default `true`), backfills from `products.in_stock`, then drops `products.in_stock`.
- **Backfill deviation:** the plan proposed a SQL join inside the migration; implemented with a PHP `chunkById` loop instead, to stay driver-agnostic (the test suite runs on SQLite `:memory:`).
- **Follow-up UX:** after the core rollout, a per-unit **"Out of stock"** label was added to the add-to-cart modal (`resources/views/livewire/product-unit-picker.blade.php`).
- **Verification at ship:** `php artisan migrate` ✓ · `php artisan test --compact` → 326/326 ✓ · `vendor/bin/pint --dirty` clean ✓ · `npm run build` ✓.

## Context

Today `products.in_stock` (boolean) is the single source of truth for stock. `Product::inStock()` returns it. The admin Prices page renders a toggle per unit row, but `Prices::toggleStock()` (`app/Livewire/Admin/Prices.php:79`) actually toggles the whole **product** (`$unit->product_id`), so all units of a product share one flag.

This plan moves stock to `product_units.in_stock` so each size/variant of a product can be independently in/out of stock.

## Confirmed Decisions

- **Drop `products.in_stock` entirely** — product availability is always derived from its units. Single source of truth.
- **Availability rule:** a product counts as "in stock" when **any** of its units is in stock. Out-of-stock sizes are disabled in pickers.
- **Baskets are untouched** — basket checkout does not check constituent product stock today, so nothing regresses.

## Implementation Areas

### 1. Database

- New migration `add_in_stock_to_product_units_table`:
  - Add `product_units.in_stock` boolean, default `true`.
  - Backfill from `products.in_stock` via join — **inside the same migration** so there is no window where a product's units read as out of stock.
  - Drop `products.in_stock`.

### 2. Models

- `app/Models/ProductUnit.php`:
  - Add `'in_stock'` to `#[Fillable]`.
  - Add `'in_stock' => 'boolean'` to `casts()`.
- `app/Models/Product.php`:
  - Remove `'in_stock'` from `#[Fillable]` and `casts()`.
  - `inStock()` becomes unit-derived and N+1-safe:
    ```php
    return $this->relationLoaded('units')
        ? $this->units->contains(fn ($unit) => $unit->in_stock)
        : $this->units()->where('in_stock', true)->exists();
    ```
  - `scopeAvailable()` → `where('is_active', true)->whereHas('units', fn ($q) => $q->where('in_stock', true))`.

### 3. Storefront consumers

- Add `->with('units')` where products are listed without it, to avoid N+1:
  - `app/Livewire/Shop.php:77`
  - `app/Livewire/Search.php:29`
  - `app/Livewire/Home.php:39` (featured)
  - `app/Livewire/ProductDetail.php:74` (related products)
- `resources/views/livewire/product-card.blade.php:10,44` — works once `inStock()` is unit-derived.
- `app/Livewire/ProductDetail.php:145` `ensureStock()` → check `$this->selectedUnit?->in_stock ?? $this->product->inStock()`.
- `resources/views/livewire/product-detail.blade.php:61,75` — In-stock indicator + Add button reflect the **selected** unit; size buttons (lines 31-41) get `disabled` + muted styling for out-of-stock units.
- `app/Livewire/ProductUnitPicker.php:74` `addToCart()` → check `$this->selectedUnit()?->in_stock`.
- `resources/views/livewire/product-unit-picker.blade.php:24-37` — disable out-of-stock options (grayed, not selectable).
- `app/Livewire/RecipeProduct.php` + `resources/views/livewire/recipe-product.blade.php:17` — check pivot unit stock: `$this->product->units->firstWhere('id', $this->unitId)?->in_stock ?? $product->inStock()`.
- `app/Livewire/RecipeShow.php:50` — `addAllToCart()` skips products whose **pivot unit** is out of stock.
- `resources/views/partials/cart-item.blade.php:9` → `@if (! $item->productUnit?->in_stock)`.
- `app/Livewire/Cart.php:28,141` — `increment()` guard + `outOfStockItems()` use `$item->productUnit?->in_stock`.
- `app/Livewire/Checkout.php:118` — `outOfStockItems()` filter on `$item->productUnit?->in_stock`.
- `app/Services/OrderService.php:24` — filter on `$item->productUnit?->in_stock ?? $item->product?->inStock()`.

### 4. Admin

- `app/Livewire/Admin/Prices.php:79` — `toggleStock(Product $product)` → `toggleUnitStock(ProductUnit $unit)` (updates `$unit->in_stock`).
- `resources/views/livewire/admin/prices.blade.php:82-91` — call `toggleUnitStock($unit->id)`, read `$unit->in_stock`.
- `app/Livewire/Admin/Dashboard.php:57` — `outOfStockProducts()` → `whereDoesntHave('units', fn ($q) => $q->where('in_stock', true))`.
- `resources/views/livewire/admin/products.blade.php:62-63` — badge keeps using `$product->inStock()` (now derived). No change.
- `app/Livewire/Admin/ProductForm.php` — remove `in_stock` from product data/validation; add `'in_stock' => true` to each `unitRows[]` entry; `syncUnits()` writes it.
- `resources/views/livewire/admin/product-form.blade.php:90-93` — remove product-level checkbox; add per-unit "In stock" checkbox inside each unit row (lines 54-85).

### 5. Seeders / factories

- `database/seeders/ProductSeeder.php` — drop `'in_stock'` from the product `updateOrCreate`; add `'in_stock' => true` to the unit `updateOrCreate`.
- `database/factories/ProductFactory.php`:
  - `available()` sets the created unit in stock instead of `in_stock`.
  - Add an `outOfStock()` state that sets the unit's `in_stock` false.
  - `configure()` / `withUnits()` set unit `in_stock` (default true).
- `database/factories/ProductUnitFactory.php` — add `'in_stock' => fake()->boolean(80)`.

### 6. Tests

- `tests/Feature/AdminTest.php:156-163` — set `unitRows.0.in_stock` in the form test; `:367-374` — `toggleStock` → `toggleUnitStock($unit)`.
- `tests/Feature/OrderTest.php:487,511,556,573` — `in_stock => false` products → `outOfStock()` factory state.
- `tests/Feature/RecipeTest.php:254` (Mint out of stock) → `outOfStock()`.
- `tests/Feature/SearchTest.php:30` → `outOfStock()`.
- Add: unit-level toggle test in `AdminTest`; a mixed in/out-of-stock-unit product asserting "any unit in stock" on the card.

## Deployment Notes

These notes were written pre-release; the migration and code were shipped together in a standard release, so the code/migration window concern no longer applies.

App-level transactions are **safe**:

- Orders are created inside `DB::transaction()` and check stock at placement time; unit-level checks preserve that guarantee.
- `order_items` snapshots `unit` and `price` at order time (`OrderService.php:79-87`), so historical orders are unaffected by later stock changes.
- `cart_items` already stores `product_unit_id`, so existing carts keep working.

The real hazard is the **migration vs. running code** window:

- Migration first + old code live → `SQLSTATE: column not found` on `products.in_stock` (shop, cards, cart, checkout all break).
- New code first + migration not run → new code reads `product_units.in_stock`, which does not exist yet.

Not zero-downtime deployable as-is. Choose one:

1. **Short maintenance window (simplest):** `php artisan down` → `php artisan migrate --force` + deploy code → `php artisan up`.
2. **Two-phase deploy (no downtime):**
   - Phase A: add `product_units.in_stock` + backfill (additive, safe).
   - Deploy code that reads unit-level and stops using `products.in_stock`.
   - Phase B (later release): drop `products.in_stock`.

Catalog tables are tiny here (adding a column with a default is instant; `DROP COLUMN` rewrites the table on MySQL but is fast at this scale), so the risk is the code/migration window, not the lock.

## Verification

- `php artisan migrate`
- `php artisan test --compact`
- `vendor\bin\pint --dirty --format agent`
- `npm run build`