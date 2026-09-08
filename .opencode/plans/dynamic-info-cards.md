# Dynamic Info Cards Plan

## Goal
Admin-editable info-card copy (6 cards on home, basket-show, product-detail)
backed by the settings table. Icons included via whitelist dropdown — no
build needed on change (see constraint).

## Constraint (do NOT forget)
Lucide resolves `data-lucide` names in-browser against the app.js registered
set. The admin dropdown is whitelisted to pre-registered names ONLY, so icon
changes never need a build. A brand-new icon = app.js import + npm run build
(dev task, never an admin action).

## Design (owner-approved)
1. Storage: single `info_cards` JSON key (6 × {icon,title,subtitle}).
   `SettingsServiceProvider` maps it to `config('mart.info_cards')` — zero
   extra queries (provider already plucks all settings per request).
2. `App\Support\InfoCards`: DEFAULTS (today's copy) + ICONS whitelist +
   `all()` (DB JSON over defaults, sanitized).
3. `x-info-cards` component (compact prop) replaces tripled markup ×3 blades.
4. `Admin\Settings` mount/save + blade "Info cards" section (6 rows).
   Titles required/max:60, subtitles nullable/max:80, icon in-whitelist.
5. Tests: save→custom renders ×3 pages; defaults when unset. Suite + pint.

## Status: EXECUTED 2026-09-08 (uncommitted)
