# Server Upload Prep Plan (manual copy, DB dump travels later)

## Goal
Local working tree verified as the upload-ready "final version". Owner uploads
files manually and commits personally. No commits from agent, no optimize
(owner runs it on the server later — NEVER upload local bootstrap/cache/).

## Constraints (do NOT forget)
- Upload method is manual file copy: deletions do NOT propagate. The
  DELETE kill list (§4) must be applied by hand on the server.
- Server DB is never migrated: owner uploads a copy of the local DB AFTER
  the product import. No migrate --force on the server for this round.
- Never touch on server: `.env`, `storage/` contents.

## Steps
1. `npm run build` (public/build stale: blades 9/7 5:08 PM > assets 12:07 PM).
2. Final stamp: full suite 436 green + `migrate:status` clean + pint clean.
3. Generate upload pack lists (§4) from git status + untracked files.
4. Owner server steps: upload pack → delete kill list → storage-link check →
   smoke-test homepage + admin login.

## Upload pack (generated 2026-09-08 — see execution notes)
- ADD/replace: all files in `git diff --name-only --diff-filter=AM origin/main...HEAD`
  (app, routes, resources/views, database/migrations incl. the 4 new 08000*
  files + 3 edited creates, database/seeders, factories) PLUS the entire
  `public/build/` folder (gitignored — never forget it).
- SKIP (harmless but pointless on server): `.opencode/`, `docs/*.md`,
  `docs/*.csv`, `tests/`.
- DELETE on server (kill list — FTP knows no renames; remove old names even
  though git recorded 060819->080002 and 121042->080004 as renames):
  database/migrations/2026_09_02_053633*,
  2026_09_02_053655*, 2026_09_02_065121*, 2026_09_03_054728*,
  2026_09_03_054729*, 2026_09_03_054730*, 2026_09_03_054731*,
  2026_09_03_070419*, 2026_09_03_072146*, 2026_09_03_072147*,
  2026_09_03_072148*, 2026_09_03_120601*, 2026_09_03_121041*,
  2026_09_03_121042*, 2026_09_05_053808*, 2026_09_05_053809*,
  2026_09_05_060819*, docs/phase2-unpushed.sql,
  docs/phase2-delivery-slot-fix.sql, app/Livewire/Admin/Units.php,
  resources/views/livewire/admin/units.blade.php,
  `resolveSingleFileComponentPath(privacy-policy))` (if present).

## Out of scope
Product import (Book1/Book2.csv mapping), DB dump + upload, optimize run.
Optimize runs ON the server; uploading local bootstrap/cache/ would ship
Herd paths + local credentials baked into config.

## Status: EXECUTED 2026-09-08
- `npm run build` clean (fresh hashes app-DaiAUnZO.css / app-D8_7ie7O.css /
  app-D0GpDET6.js). Full suite 436/436 green. `migrate:status` all Ran.
  Pint clean. Owner committed consolidation as 430f84d personally.
