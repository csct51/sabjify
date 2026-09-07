# Plan: recipe links on baskets + recipe adds enforce like products

Status: **Implemented** — executed after user approval.

## Implementation Record

- **Schema:** `2026_09_05_121955_create_basket_recipe_table` (basket ↔ recipes, cascade both, unique pair). Ran clean via `migrate --force`.
- **Models:** `Basket::recipes()` + `Recipe::baskets()`.
- **Admin `BasketForm`:** recipes searchable checkbox list + linked panel (`removeRecipe`), `sync()` on save, mount hydrates (inactive still shown with "Hidden in store" flag), `recipes.*` validation.
- **Store:** "What you can make" card section on basket page (image, title, snippet, View recipe →), active-only.
- **Enforcement:** `RecipeShow::addAllToCart` skips toggle-off/short stock (coverage summed across direct + recipe rows, same message); `RecipeProduct::addToCart`/`increment` mirror `ProductCard`; blade gates controls on packs + shows Out of Stock + error line (flex-wrap fix for placement).
- **Tests:** attach/detach, cards render/hide, add-all skip, single add block. Suite 420/420 ✓ (1428 assertions), pint clean ✓. No build (existing classes/icons).

## 1. Goal (user-locked)

Baskets link to recipes as inspiration ("what you can make") — display-only, fully decoupled from basket products and inventory. Separately: recipe ingredients ARE individual products, so adding them from recipe pages enforces identically (same gates, messages, helpers; `recipe_id` stays grouping metadata only).

## 2. Standing rules (do NOT forget)

- Every change tested (Pest). Pint after PHP changes. One additive migration + `migrate --force`.
- **Phase 2 — Live Database Rules** (`AGENTS.md:184-190`): NEVER `migrate:fresh/refresh/reset`, `db:wipe`.
- Related plans: `basket-inventory` (implemented; sale math untouched by this plan).

## 3. Locked decisions (user-confirmed)

- Multiple recipes per basket (`basket_recipe` pivot, pure links).
- Store shows a recipe-card section (image, title, snippet, View recipe); inactive recipes hidden.
- Recipe adds: skip short/toggled-off ingredients in add-all (same message); single add/increment mirrors `ProductCard`; coverage sums across direct + recipe cart rows of same product+unit.

## 4. Implementation areas

### 4.1 Schema (1 additive migration)

- `create_basket_recipe_table`: `basket_id` → cascade, `recipe_id` → cascade, timestamps, unique pair. Down: drop.

### 4.2 Models

- `Basket::recipes()` + `Recipe::baskets()` BelongsToMany.

### 4.3 Admin BasketForm

- Recipes multi-select (searchable checkbox list mirroring products: image, title); `sync()` on save; mount hydrates; `recipes.*` validated `exists:recipes,id`. Active listed; linked-inactive still shown selected.

### 4.4 Store basket page

- "What you can make" section after What's inside, active recipes only. No price/stock interplay.

### 4.5 Recipe enforcement (no migrations)

- `RecipeShow::addAllToCart`: skip on toggle-off OR uncovered stock (existing cart summed across product+unit rows); same skipped message.
- `RecipeProduct::addToCart`/`increment`: mirror `ProductCard` (combined check, same messages).

### 4.6 Tests

Attach/detach via form; cards render/hide by active flag; add-all skips short (message intact); single add/increment blocks; suite + pint. No build (existing classes/icons).

## 5. Out of scope

Inventory explode for recipe links, selling report, basket price math, recipe page redesign, CSV, toggle semantics.
