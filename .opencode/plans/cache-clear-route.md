# Cache-Clear Route Plan (v3 — diagnose first, clear second)

## Goal
Browser-triggered diagnostic + cache clear for a terminal-less server,
shipped as a TEMPORARY standalone file. Deleted after the incident is
confirmed fixed (tracked here so it is not forgotten).

## Constraint (do NOT forget)
- Server runs PRE-consolidation code (nothing uploaded since). Deliverable
  is old-tree compatible: framework facades only, zero app classes.
- Owner uploads: `routes/cache-clear.php` + one require line in the
  server's routes/web.php + `docs/phase2-delta.sql` (run order: diagnose →
  SQL → re-diagnose). Backup first.
- Products cannot be affected: zero writes to business tables; cache flush
  touches only cache stores / compiled views / bytecode.

## Implementation (executed 2026-09-08)
- `routes/cache-clear.php`: `GET /cc/{token}` (token in chat only, never in
  repo docs), hash_equals gate, throttle. Diagnosis section (deployed-code
  probe, compiled-view freshness, config/route cache presence, OPcache
  timestamp validation, storage link, SELECT-only DB probes, context) +
  actions section (view counts, view:clear, optimize:clear, opcache_reset)
  + post-clear re-diagnosis. Single JSON response.
- Required from routes/web.php (one line). Removal: delete require line +
  file after owner confirms green (or consolidation upload overwrites).

## Status: BUILT 2026-09-08 (uncommitted; token issued in chat, not in repo)
