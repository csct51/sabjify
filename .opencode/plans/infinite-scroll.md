# Infinite Scroll Plan (shop + search, no button)

## Goal
Pure infinite scroll on shop + search pages. Remove the Load more buttons;
auto-load on scroll with an end-of-list line.

## Root cause (do NOT forget)
Both blades already had `x-intersect.*` auto-load sentinels — dead markup
because the Alpine intersect plugin was never installed (Livewire bundles
core Alpine only). The buttons did 100% of loading. Server-side
loadMore()/hasMore/loadingMore were already correct and tested.

## Decisions (owner-locked)
- Trigger: built-in IntersectionObserver in app.js (existing reveal-utility
  pattern), zero new dependencies. No Alpine plugin.
- Scope: shop + search only. recipes/index keeps its button (same pattern,
  follow-up on request).

## Implementation
1. app.js `initInfiniteScroll()`: observer on [data-infinite-sentinel],
   fires once per sentinel, resolves component via closest [wire:id] +
   Livewire.find(), calls loadMore() only when hasMore && !loadingMore.
   MutationObserver picks fresh sentinels post-morph. Hooks: livewire:init,
   navigated, morph.added/updated. Then `npm run build` (mandatory).
2. shop.blade + search.blade: button blocks become sentinel spinner (while
   hasMore) + "You've seen all N items" end line when exhausted.
3. Tests: Load more absent both pages; end-line appears when exhausted;
   existing loadMore-call tests green. Full suite + pint + owner scroll pass.

## Status: EXECUTED 2026-09-08 (uncommitted)

NOTE: one full-suite run mid-session showed 2 AuthTest failures with a 3x
duration (122s) — AuthTest green in isolation, and two consecutive full runs
green after. Verdict: transient environment stall (flake), not a defect.
No code path from this change reaches Auth (JS + 2 blades + Shop/Search
tests only).
