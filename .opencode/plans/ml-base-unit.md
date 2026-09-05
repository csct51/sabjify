# Plan: add `ml` base unit (dormant, nothing else)

Status: **Implemented** — executed after user approval.

## Implementation Record

- Added `case Millilitre = 'ml'` + `label()`/`purchaseUnit()` arms (`app/Enums/BaseUnit.php`); also normalized `purchaseUnit()` from ternary to `match` for exhaustiveness. Dormant: UI selects unchanged, no migrations, no behavior change.
- Verification: full suite 370/370 ✓ (1254 assertions), pint clean ✓.

## 1. Goal (user-locked minimal scope)

Add `ml` as a base unit case so litres can be introduced in future without headache. Nothing else: no `litre` unit row, no child units (no `500 ml` etc.), no blade/UI changes, no display or validation changes, no new tests.

## 2. Standing rules (do NOT forget)

- **Phase 2 — Live Database Rules** (`AGENTS.md:184-190`): NEVER `migrate:fresh/refresh/reset`, `db:wipe`. Additive migrations only + `migrate --force` on prod. (This plan needs **zero** migrations: both `units.base_unit` and `products.base_unit` are plain strings with a PHP-enum cast.)
- **Plan workflow:** plans live in `.opencode/plans/` (no timestamp, just plan name). Finish plan file first, then **ask whether to execute; only execute on explicit yes**.
- **Developer note (permanent):** `To add a new base unit, add a new case to BaseUnit enum.` — this plan IS that operation. Lives in `app/Enums/BaseUnit.php` docblock + `units/create:30`, `units/edit:30`, `product-form:59`.

## 3. Change (3 lines, `app/Enums/BaseUnit.php` only)

```php
case Millilitre = 'ml';                       // alongside Gram / Piece

// label()
self::Millilitre => 'ml',

// purchaseUnit()
self::Millilitre => 'litre',
```

The two match arms are part of the case itself — without them any use of the case throws `UnhandledMatchError`. (PHP rules: TitleCase enum keys.)

## 4. Why this is safe and sufficient

- `Rule::enum(BaseUnit::class)` (Units Create/Edit, ProductForm) would accept `ml` automatically — but the base-unit `<select>`s stay hardcoded to `g`/`piece`, so `ml` cannot be picked anywhere in UI yet. Fully dormant, zero behavior change.
- No `litre` row and no child units per explicit user decision (`seed litre row` option rejected). If litre conversions are ever needed before a row exists, `Unit::factorFor()` code fallback is the established safety net — out of scope here.
- `displayStock()`, `stockInUnit()`, `qtyRulesForUnit()`, form hints, factories, storefront: untouched by decision.

## 5. Verification

- `php artisan test --compact` — full suite must stay green (no behavior change).
- `vendor/bin/pint --dirty --format agent` — style clean.
- No build (no blade/class changes beyond the enum).

## 6. Out of scope (explicit)

Litre reference row, ml child units, blade select options, display/stock-report changes, validation message changes, new tests, storefront, repair/backfill migrations.
