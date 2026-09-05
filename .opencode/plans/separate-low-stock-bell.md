# Plan: separate low-stock bell from orders bell

Status: **Implemented** — executed after user approval.

## Implementation Record

- `NotificationBell` reverted to pending-orders-only (badge, modal, title).
- `Product::scopeLowStock()` added; `Reports/Stock` private duplicate removed in favour of it.
- New `LowStockBell` component + blade (amber triangle bell + badge, own modal with product links + filtered-report footer); header entry beside orders bell.
- `TriangleAlert` registered in `app.js`; `npm run build` clean (new bundle hash).
- **Tests:** bell test split (low-stock bell render + empty state; orders bell untouched and green). Suite 394/394 ✓ (1337 assertions), pint clean ✓.

## 1. Goal (user-locked)

Low-stock notifications get their own bell, separate from the pending-orders bell. No combined badge, no shared modal.

## 2. Standing rules (do NOT forget)

- Every change tested (Pest). Pint after PHP changes. `npm run build` after Tailwind/JS/icon changes.
- Related plans: `low-stock-alerts` (implemented — this plan splits what it combined).

## 3. Implementation areas

### 3.1 Revert NotificationBell to orders-only

- `app/Livewire/Admin/NotificationBell.php`: remove `lowStockCount`, `lowStockProducts`, `lowStockScope`, `Product`/`Builder` imports if unused.
- `notification-bell.blade.php`: badge back to `pendingOrdersCount`; remove low-stock section + report footer; title back to "Pending orders".

### 3.2 Consolidate scope on Product

- `Product::scopeLowStock(Builder $query): Builder` — `whereNotNull('low_stock')->where('current_stock', '>', 0)->whereColumn('current_stock', '<=', 'low_stock')`.
- `Reports/Stock.php` + both bells use it (Stock keeps its private wrapper or calls scope directly).

### 3.3 New LowStockBell

- `app/Livewire/Admin/LowStockBell.php` (mirrors NotificationBell): `show` toggle/close, `lowStockCount`, `lowStockProducts()` (limit 10, `orderBy('current_stock')`).
- `low-stock-bell.blade.php`: amber `triangle-alert` button + badge; own modal (product + `displayStock()`, links to product edit; footer to `admin.reports.stock?stockFilter=low`).
- Header (`layouts/admin.blade.php`): `<livewire:admin.low-stock-bell />` beside `<livewire:admin.notification-bell />`.

### 3.4 Icon + build

- `app.js`: add `TriangleAlert` to lucide import + `icons` map; `npm run build`.

### 3.5 Tests

Orders bell no longer mentions low stock; low-stock bell badge/section/empty-state; Stock counts unchanged. Full suite + pint + build.

## 4. Out of scope

Dismiss/read state, push/email alerts, storefront badges.
