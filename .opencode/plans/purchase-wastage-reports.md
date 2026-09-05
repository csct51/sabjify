# Plan: purchase report + wastage report

Status: **Implemented** — executed after user approval.

## Implementation Record

- `Reports/Purchases` + blade (`admin.reports.purchases`, sidebar "Purchase Report", `boxes` icon): per-product qty/base/spent, header spent/entries/base, Today/7d/30d/custom dates, supplier/category/search filters.
- `Reports/Wastage` + blade (`admin.reports.wastage`, sidebar "Wastage Report", `trash-2` icon): per-product qty/base/entries, header base/entries/products, dates, dynamic reason dropdown, category/search.
- Same structure as Selling: cursor aggregation, manual AJAX pager (plain `links()` would full-reload and reset filters), amount-desc sort. No CSV v1.
- **Tests:** 3 new (purchase math + supplier/date filters, wastage math + reason filter). Suite 392/392 ✓ (1330 assertions), pint clean ✓. No migrations, no build.

## 1. Goal

Mirror the selling report: per-product purchase and wastage reports with header totals and filters. Entry-level detail stays on existing Index/Show pages.

## 2. Standing rules (do NOT forget)

- **Phase 2 — Live Database Rules** (`AGENTS.md:184-190`): NEVER `migrate:fresh/refresh/reset`, `db:wipe`. Additive migrations only + `migrate --force` on prod. (This plan needs **zero** migrations.)
- Related plans: `storefront-inventory` (selling report pattern source), `db-driven-base-units`, `drop-unused-columns`, `low-stock-alerts` (all implemented).

## 3. Purchase report (`admin.reports.purchases`, sidebar "Purchase Report")

- Rows per product: qty (purchase-unit), base qty (snapshot), amount spent (sum `line_total`).
- Header: total spent, purchase entries, base qty total.
- Filters: Today/7d/30d presets + custom range on `purchase_date`, supplier dropdown, category, product search.
- Source: `PurchaseItem` + purchase + `product.category`. Deleted purchases vanish (hard deletes).

## 4. Wastage report (`admin.reports.wastage`, sidebar "Wastage Report")

- Rows per product: qty, base qty, entries count.
- Header: total base wasted, wastage entries, products affected.
- Filters: date presets + custom on `wastage_date`, reason dropdown (distinct reasons), category, product search.

## 5. Implementation notes

- Same structure as `Reports/Selling.php`: cursor aggregation, manual `LengthAwarePaginator` + `previousPage`/`nextPage` wire buttons (plain `links()` would full-reload and reset filters), revenue/amount-desc sort.
- Sidebar icons reuse registered ones (`boxes`, `trash-2`).
- No CSV v1.

## 6. Tests (Pest, sqlite memory)

Report math (0.5 kg ×2 → base 1000, spent = rate×qty), supplier/reason/date filters narrow, empty states. Full suite + pint.

## 7. Verification & rollout

`vendor/bin/pint --dirty --format agent`, `php artisan test --compact`. No migrations, no build (existing classes/icons only). Commit only when explicitly requested.
