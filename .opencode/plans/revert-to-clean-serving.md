# PLAN: Revert-to-clean serving (Option 1 — single truth)

## Status: SUPERSEDED by subfolder-serving regime (see bottom). History kept;
## do not execute as written — the panel docroot move never landed and is no
## longer required.

## Goal
Prod docroot serves `public/` directly. All `/public`-prefix hacks removed
from code. Bare `APP_URL` both sides. Local Herd and prod render
byte-identical image/asset addresses — parity restored, local verification
meaningful again.

## Locked decisions (do NOT re-litigate)
- Per-tag hardcodes, config hardcodes, `ASSET_URL` mechanism: all reverted,
  none kept.
- Server `.env`: `APP_URL=https://sabjify.com` (bare, owner-confirmed). No
  `ASSET_URL` line anywhere. `APP_DEBUG=false`, `APP_ENV=production`
  advised alongside.
- Docroot move is the owner's panel step and is ASSUMED DONE before upload
  (verified by: shop loads at bare domain).
- Local dev stays untouched by design (Herd serves `public/`; bare URLs
  already correct here).

## Build session scope (code only, no server access)
1. Blades (~40 tags, 27 files): reverse
   `str_replace('/storage/', '/public/storage/', EXPR)` to plain `EXPR`;
   `asset('public/storage/...')` to `asset('storage/...')`. TARGETED
   reverse-edits only — info-cards component swaps and all other legit work
   in the same files stays untouched. Files: home, shop, categories/index,
   product-card, product-detail (x4), product-unit-picker, basket-card,
   basket-show (x3), cart, recipes/index, recipe-show, recipe-product,
   orders/show, 3 cart/order partials (x4), admin products/product-form/
   prices/baskets/basket-form (x3)/recipes/recipe-form (x2)/order-show/
   stock/category-form, components/logo.
2. Config/model (3): `filesystems.php` disk `url` to `APP_URL.'/storage'`;
   drop the `asset_url` key addition from `config/app.php`;
   `Setting::logoUrl()` to `'/storage/'.$value`.
3. Tests (2): logo expectation + serving paths back to unprefixed form.
4. Proof gate: grep sweeps for `/public/storage`, `public{{`, `ASSET_URL`
   return empty across code+tests; full suite + pint green. Green run IS
   the parity certificate.

## Owner side, ordered (after build ships)
1. Panel docroot to `public/`; verify bare-domain shop load.
2. Server `.env` as locked above; delete `bootstrap/cache/config.php` if
   present.
3. Upload reverted tree; hard-refresh both machines; compare one product
   image address side by side (same string, both loading).

## Explicitly out of scope
Seed/DB, WhatsApp, info cards, pickers, SQL files, diagnostic leftovers
(already removed), any new feature. No `npm build` needed (no frontend
sources change).

## Status: PLANNED (parked — execute on owner "go")

---

# ACTIVE REGIME: subfolder serving (supersedes everything above)

## Goal
Prod stays served from a subfolder-style layout. Unprefixed image/asset
addresses work on BOTH machines with zero prefixes in code and zero
symlinks: Laravel's built-in disk-serve route (`/storage/{path}`, served
from disk by code) answers what static serving cannot reach.

## Locked decisions (do NOT re-litigate)
- No `/public` prefix ANYWHERE: not per-tag, not config, not tests.
- Root `.htaccess` (Claude's file, one-word edit): delete `storage|` from
  the line-18 block list so `/storage/*` reaches Laravel instead of 403.
  Everything else in that file byte-identical. Rationale recorded: the
  block only ever fired for nonexistent paths (line 15 serves existing
  files first), so removal changes missing-file 403s into proper 404s and
  enables the serve route — zero disclosure delta ( dot-paths normalize
  into Laravel 404s, PUT upload route is signature-gated, traversal
  throws 404; verified in ServeFile.php / ReceiveFile.php ).
- Server `.env`: `APP_URL=https://sabjify.com` (bare). No `ASSET_URL`.
- Per-tag hardcodes reverted to plain expressions (same file list as
  §"Build session scope" above — that section's revert inventory is
  re-used verbatim, only the rationale changes).
- Local dev needs nothing (Herd serves `public/`; unprefixed already
  correct here). No symlink required on either machine, ever.

## Owner side, ordered
1. Upload edited root `.htaccess` (back up current text first).
2. Server `.env` bare as above; delete `bootstrap/cache/config.php` if present.
3. Hard-refresh; open one product image (unprefixed address, loading).

## Status: SAVED (execute code + htaccess edit on owner "go")
