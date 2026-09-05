# Plan: kg display for gram-base rows, drop Base Qty columns

Status: **Implemented** — executed after user approval.

## Implementation Record

- **Selling:** rows carry purchase-unit-normalized `qty` (mixed denominations converted, e.g. 2×1 kg → `2 kg`); `packs` int-truncation bug fixed en passant (0.5 kg rows no longer count 0); Base Qty column/keys removed; header `Packs Sold` → `Qty Sold`.
- **Purchase:** float `qty` kept, Base Qty column/keys/summary removed; header third card → `Products` distinct count.
- **Wastage:** same row treatment; summary/header drop base (2 cards: Entries, Products Affected).
- **Display rule** via `Unit::integerOnlyFor()`: integer bases `3 × piece`, others `0.5 kg` — no hardcoded bases.
- **Tests:** display assertions use computed `rows()` + unescaped `>kg<` (morph markers defeat raw matching; Livewire escapes `<>` needles by default). Suite 394/394 ✓ (1349 assertions), pint clean ✓. No migrations, no build.

## 1. Goal (user-locked)

In reports, gram-base (non-integer) products show direct amounts (`0.5 kg`) instead of packs format (`3 × 1 kg`); piece-base keeps packs. Remove Base Qty columns/headers.

## 2. Standing rules (do NOT forget)

- Every change tested (Pest). Pint after PHP changes. No migrations, no build (existing classes only).
- Related plans: `storefront-inventory` (selling report), `purchase-wastage-reports`.

## 3. Rule (generic, no hardcoded bases)

`Unit::integerOnlyFor($purchaseUnit)` decides: integer bases → `{qty} × {unit}`; others → `{qty} {unit}`.

## 4. Implementation areas

### 4.1 Selling (`Reports/Selling.php` + blade)

- Rows: `packs` (int-truncated — loses fractional qtys like 0.5 kg!) → `qty` float sum (round 3); drop `base_qty` key + fallback branch.
- Blade Qty cell: integer → `3 × piece`, else `0.5 kg`; remove Base Qty th/td.
- Header `Packs Sold` → `Qty Sold` (summary key renamed `packs` → `qty`).

### 4.2 Purchase (`Reports/Purchases.php` + blade)

- Rows: keep float `qty`; drop `base_qty` key.
- Blade Qty cell: same rule; remove Base Qty th/td.
- Summary: drop `base_qty`, add `products` distinct count; header third card `Base Qty` → `Products`.

### 4.3 Wastage (`Reports/Wastage.php` + blade)

- Rows: keep float `qty`; drop `base_qty` key.
- Blade Qty cell: same rule; remove Base Qty th/td.
- Summary: drop `base_qty`; header drops to 2 cards (Entries, Products Affected).

## 5. Tests

g-row shows `0.5 kg`, piece-row keeps `× piece`, no `Base Qty` text on all three pages, headers render. Full suite + pint.

## 6. Out of scope

Aggregation math, filters, pagination, Show pages, entry forms, mixed-unit-same-product first-seen-unit quirk.
