# Info Cards Page Plan (v2 — dedicated table, owner-override)

## Goal
Info cards out of Settings into their own Catalog page, stored in a
DEDICATED `info_cards` table (owner explicitly chose table over settings
key/value despite the simpler fit — recorded, not re-litigated).

## Design (owner-locked)
1. Migration `create_info_cards_table`: id, position (tinyint unique 1-6),
   icon, title, subtitle nullable, timestamps. Deletes legacy `info_cards`
   settings key if present.
2. `InfoCard` model (Fillable). No seeder: helper falls back to DEFAULTS
   when rows are missing (fresh installs + tests render current copy).
3. `InfoCards::all()` reads the TABLE (same return shape + sanitization);
   provider JSON mapping removed. `x-info-cards` + whitelist unchanged.
4. `Admin\InfoCards` page in Catalog (`admin.info-cards`, layout-grid icon):
   6 positional rows, one save → validate → updateOrCreate by position.
5. Strip infoCards from `Admin\Settings` + blade (v1 step, unchanged).
6. Tests: InfoCardsTest adapted (save→rows→renders; defaults on empty;
   bad icon rejected). Migrate (plain, additive) + suite + pint.

## Status: EXECUTED 2026-09-08 (uncommitted)
