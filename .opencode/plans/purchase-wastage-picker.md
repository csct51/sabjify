# Purchase/Wastage Picker Plan (Slim Select 4)

## Goal
Fix the product-picker mouse-click race, clear-on-add, searchable supplier
(closed list), and rate autofill (default unit price, editable) in Purchases
+ Wastages Create/Edit — using Slim Select 4 (owner-approved npm dependency).

## Scope lock (do NOT forget)
- Slim Select is used ONLY in the 4 inventory forms. No global rollout.
- The hand-rolled Alpine combobox retires because its only usages are those
  forms. BasketForm/RecipeForm server-side search stays untouched.

## Decisions (owner-locked)
- Library: Slim Select 4 (npm). No composer package exists for this; UI kits
  (MaryUI/WireUI/Flux Pro) rejected as overkill.
- Rate source: default (first) selling unit price.
- Supplier: closed list (Cash + registered), new suppliers via Suppliers admin.

## Implementation
1. `npm install slim-select`; import JS+CSS via Vite with brand theme vars.
2. `x-admin.searchable-select` wrapper: native select in wire:ignore,
   per-instance init, change→$wire.set bridge, guarded server→client sync,
   destroy() on morph.removed. Handles single-value pick + clear + reset.
3. Product picker: options id/label(en name)/sub(category)/search text
   (name + category + alternate_names) so Hindi search works.
4. Supplier picker: name options, Cash pinned first.
5. `updatedFormProductId()` fills formRate (null-guarded); `addProduct()`
   dispatches `product-added` → wrapper resets select.
6. Dead-code sweep: productSearch prop, products() computed,
   calculateBaseQty(), dead search= prop (4 classes + blades).
7. Delete Alpine product-search component + blade; `npm run build`.

## Tests
New: rate autofill on select; custom rate survives add; product-added
dispatched; supplier save unchanged (existing). Full suite + pint.
Browser click/search verified manually by owner on local (no JS runner).

## Status: EXECUTED 2026-09-08 (uncommitted)
