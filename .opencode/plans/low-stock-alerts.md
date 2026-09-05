# Plan: per-product `low_stock` + bell alerts

Status: **Implemented** — executed after user approval.

## Implementation Record

- **Migrations shipped:** `2026_09_05_053808_add_low_stock_to_products_table` (DDL, nullable decimal) + `2026_09_05_053809_backfill_products_low_stock` (DML nulls-only: g→1000, piece→10, ml→1000). Ran clean via `migrate --force`.
- **Model:** fillable/cast/phpdoc + `isLowStock()` (null = never low; epsilon-safe boundary).
- **ProductForm:** `lowStock` property, mount hydrate (blank when null), `nullable|numeric|min:0` rule, persist rounded-or-null; blade "Low stock alert at" beside Stock Quantity with base suffix.
- **Stock report:** summary + filter via `whereNotNull→>0→whereColumn <=`; labels `Low (1-10)` → `Low Stock`/`Low`; row badges via `isLowStock()`; `stockFilter` gained `#[Url]` so the bell's `?stockFilter=low` link lands filtered.
- **Bell:** `lowStockCount`/`lowStockProducts()` (limit 10), combined badge, amber Low-stock section linking to product edit + report footer. Same live-computed pattern, no tables.
- **Tests:** 5 new (persist, null/boundary semantics, backfill, report filter, bell render). Suite 381/381 ✓ (1303 assertions), pint clean ✓. No build (existing classes only).

## 1. Goal (user-locked)

Replace the hardcoded low-stock `10` with a per-product `low_stock` column the admin defines (no hardcoding). Show low-stock products as notifications in the admin bell.

## 2. Standing rules (do NOT forget)

- **Phase 2 — Live Database Rules** (`AGENTS.md:184-190`): NEVER `migrate:fresh/refresh/reset`, `db:wipe`. Additive migrations only + `migrate --force` on prod.
- **Push state:** nothing uploaded to production since `deaec59` — all Phase 2 migrations unpushed.
- Related plans: `db-driven-base-units` (implemented), `drop-unused-columns` (implemented).

## 3. Implementation areas

### 3.1 Database

1. `add_low_stock_to_products_table` (DDL): `$table->decimal('low_stock', 12, 3)->nullable()->after('current_stock')`. Down: drop column. Null = no low alert (only out-of-stock).
2. `backfill_products_low_stock` (DML, separate file, nulls only): base `g` → `1000`, `piece` → `10`, `ml` → `1000`, else leave null. Down: no-op with comment.

### 3.2 Model

- `Product`: fillable + `'low_stock' => 'decimal:3'` cast + phpdoc; new `isLowStock(): bool` — `low_stock !== null && stock > 0 && stock <= low_stock` (epsilon-safe float compare).

### 3.3 ProductForm + blade

- `lowStock` string property; mount hydrates trimmed value (empty string when null); rule `nullable|numeric|min:0|max:99999999`; persist `round(...,3)` or null when blank.
- Blade "Low stock alert at" field next to Stock Quantity with base-unit suffix + hint (blank = no low alert).

### 3.4 Stock report (replaces hardcoded 10)

- `Reports/Stock.php` summary `low` + filter: `whereNotNull('low_stock')->where('current_stock', '>', 0)->whereColumn('current_stock', '<=', 'low_stock')`.
- Blade: card/filter labels `Low (1-10)` → `Low`; row badge logic via `isLowStock()` (keep `Low (qty)` converted display; `> threshold` = in stock).

### 3.5 Bell (live-computed, no tables — same pattern as pending orders)

- `NotificationBell`: `lowStockCount` + `lowStockProducts()` (limit 10); badge = pending + low combined; modal gains amber "Low stock" section (product + `displayStock()`, links to product edit); pending section untouched.

### 3.6 Tests (Pest, sqlite memory)

Form persists threshold; null = never low (zero still out); boundary `==` counts low; backfill sets base defaults; bell badge/section render; report filter narrows; full suite + pint.

## 4. Verification & rollout

`vendor/bin/pint --dirty --format agent`, `php artisan migrate --force`, `php artisan test --compact`. No build (existing classes only). Prod: upload + `migrate --force`. Commit only when explicitly requested.

## 5. Out of scope

Dismiss/read state, push/email alerts, per-base thresholds, storefront badges.
