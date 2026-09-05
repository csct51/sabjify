# Plan: basket inventory with base-constrained unit dropdown

Status: **Implemented** — executed after user approval.

## Implementation Record

- **BasketForm:** unit dropdown constrained to same-base list (`basketUnitsFor()`, selected always included so legacy rows render); pre-selects first in-base unit (was: vague empty "Default" option shown twice); custom unit text → numeric qty in purchase units with per-base hint (`qtyHintFor`) + whole-number enforcement for integer bases; save composes `"{n} {purchaseUnit}"` (exact factor, `product_unit_id` null) or dropdown path; mount parses composed values back, blanks resolved-matching and legacy text; strict same-base validation on save (legacy remaps on next edit).
- **Models:** `Basket::baseShareFor()` (composed → exact; linked → `toBaseQty`; legacy text → `factorFor` fallback), `constituentShares()`, `basketsSellable()` (min packs), `constituentsInStock()`, `canSell()`; `Unit::factorFor()` gained `litre`/`ml` arms.
- **Sale wiring:** `BasketShow`/`BasketCard` caps + "Only X baskets left" (+ `cartError` on show); `Cart::increment` + Cart/Checkout `outOfStockItems` cover basket rows; `OrderService` atomic constituent check + decrement in the same transaction, cancel restores via current pivot (documented approximation); selling report still skips baskets.
- **Also:** stale purchases-index subtitle updated (stock is wired now).
- **Tests:** 6 new basket-inventory tests (dropdown scope, numeric compose + fraction rejection, exact deduction, cancel restore, short-stock blocks at show + service, custom-number deduction). Suite 403/403 ✓ (1374 assertions), pint clean ✓. No migrations, no build.

## Follow-up: decimal custom qtys compose in base sub-units (REVERTED — wrong layer)

- Was implemented in admin save (`0.5` → `500 g` pivot) but the ask was storefront display. Reverted: save composes `"{n} {purchaseUnit}"` again; parse-back + `baseShareFor()` base-form branches removed; tests restored.

## Follow-up: sub-1 kg/litre display on basket storefront (the actual ask)

- New `Unit::displayUnitFor()`: `{n} {kg|litre}` with n < 1 renders in base sub-unit (`0.75 kg` → `750 g`, `0.5 litre` → `500 ml`); n ≥ 1, piece, and free text untouched. Base resolved from is-base rows with `kg→g`/`litre→ml` code fallback.
- Applied: `basket-show.blade.php` constituent `$displayUnit` + `BasketCard::resolveUnitName()` (same strings on cards).
- **Tests:** helper matrix + show-page render (`750 g`, no `0.75 kg`). Suite 414/414 ✓, pint clean ✓.

## 1. Goal (user-locked)

Selling a basket decrements each constituent product's stock. Custom unit is a **number entered in the product's purchase unit** (kg / litre / piece — same philosophy as purchase forms), converted to base on save; custom price stays free. Legacy free-text rows grandfathered. Basket toggle stays master; selling report keeps skipping baskets.

## 2. Standing rules (do NOT forget)

- Every change tested (Pest). Pint after PHP changes. No migrations (pivot columns already exist).
- **Phase 2 — Live Database Rules** (`AGENTS.md:184-190`): NEVER `migrate:fresh/refresh/reset`, `db:wipe`.
- Related plans: `storefront-inventory` (product wiring pattern), `db-driven-base-units`, `drop-unused-columns`, `low-stock-alerts` (all implemented).

## 3. Locked decisions (user-confirmed)

- Explode constituents on sale (decrement at placement, restore on cancel, same transaction as product lines).
- Custom qty is numeric in purchase units: g-base type `0.5` → `0.5 kg` → 500 g/basket; ml-base `0.5` → `0.5 litre` → 500 ml; piece-base whole numbers only (base row `integer_only`); blank = dropdown unit path. Hint under the field reuses `Unit::qtyHintFor()` per base.
- Unit dropdown constrained to same-base list (pending); pre-selects first unit, no empty option (already shipped); custom price free; legacy text grandfathered via `factorFor` fallback.
- `factorFor()` gains `litre → 1000` / `ml → 1.0` arms (no `litre` row exists; code fallback carries it).
- Availability: basket active AND every constituent covers requested baskets.
- Selling report unchanged (skips basket rows).

## 4. Implementation areas

### 4.1 BasketForm (admin)

- Per-line unit field: dropdown constrained to same-base list (pending); already shipped: pre-selects first unit, no empty option, unit-less fallback note.
- Per-line custom qty: numeric input in purchase units with per-base hint (`Unit::qtyHintFor()`); label shows purchase unit, e.g. `Custom qty (kg)`. Blank = dropdown path.
- Save: number present → pivot `unit = "{trimmed} {purchaseUnit}"`, `product_unit_id = null`, base share = `toBaseQty(purchaseUnit, number)` (exact, no lookup); blank → dropdown path (pivot `unit` = chosen name, `product_unit_id` = chosen id); price logic untouched.
- Validation: custom number `numeric|min:0.001|max:99999999`, plus integer-only bases require whole numbers (via base row `integer_only`); dropdown choice must belong to the product's same-base list.

### 4.2 Helpers (`Basket`)

- `constituentShares(): array<int, float>` — product_id → base per basket: custom-number lines → exact converted number; dropdown lines → linked unit `toBaseQty × 1`; legacy text-only → `factorFor` fallback.
- `basketsSellable(): int` — min over constituents of `floor(stock / share)` (fallback may yield 1.0 factor — document; never fatal).

### 4.3 Sale wiring (storefront + service)

- `BasketShow`/`BasketCard`: stepper caps at `basketsSellable()`, "Only X baskets left" pill, add/increment blocks with error.
- `Cart::increment` + Checkout pre-check cover basket rows (skip product logic, use basket math).
- `OrderService::createFromCart`: atomic constituent check (toggle AND qty) + decrement alongside product lines, same transaction + row locks; `cancel`: restore constituents symmetrically.

### 4.4 Tests (Pest, sqlite memory)

Dropdown lists only same-base units; save links id; custom number composes exact name + base (`750` + g → `750 g`, deducts 750/basket; `0.5` + g → `0.5 kg`, deducts 500); invalid number rejected; hint matches base; sale decrements exact base per constituent; cancel restores; short constituent blocks at card/show/cart/checkout/service; legacy text row sells via fallback. Full suite + pint.

## 5. Out of scope

Basket constituent deduction history in selling report, basket-level stock column, CSV, storefront low-stock config, toggle semantics changes.
