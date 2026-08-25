# Plan: Require login on small (mobile) devices; free browsing on large devices

## 1. Goal
On **small devices**, unauthenticated store-front visitors must be redirected to login before they can explore (home, shop, search, categories, products, recipes, baskets, and legal pages). On **large devices**, browsing stays fully public (no login required). The gate is a UX/business rule, not a security boundary — desktop and APIs remain open.

## 2. Decisions (from clarifications)
- **Detection:** server-side **User-Agent** check, **no new Composer dependency** (lightweight regex).
- **Scope:** gate **all currently-public store-front routes** (incl. privacy-policy & terms-conditions) on mobile.
- **Crawlers:** **whitelist** known bot UAs so mobile SEO/indexing is unaffected.

## 3. Current state (verified)
- `routes/web.php`: public routes (home, shop, search, categories, product, recipes, recipe detail, baskets, basket detail, privacy, terms) are **ungated**; `/login` is `guest`; `/cart`, `/checkout`, `/profile/*`, `/orders/*` are behind `auth` + `user.active`.
- `bootstrap/app.php`: middleware aliases `admin`, `user.active`; `redirectGuestsTo` → `route('login')`.
- `EnsureUserIsActive` is the existing middleware pattern (`handle(Request, Closure): Response`).
- No device-detection library present.
- `PhoneLogin` redirects to `route('home')` on success (line 113) — will be enhanced to honor an intended URL.

## 4. Architecture
A new middleware `RequireLoginOnMobile` (alias `require.login.on.mobile`) wraps the public browsing routes. Logic:
1. If `config('auth.require_login_on_mobile')` is false → pass (feature toggle).
2. If request UA is a known **crawler** → pass (SEO).
3. If UA is **not mobile** (desktop/tablet-classified-as-large) → pass.
4. If `$request->user()` exists → pass.
5. Else → `redirect()->guest(route('login'))` (stores intended URL).

`/login` is **excluded** from the group so users can always reach it (otherwise infinite redirect).

## 5. Files to create / modify

### Create `app/Http/Middleware/RequireLoginOnMobile.php`
```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireLoginOnMobile
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('auth.require_login_on_mobile', true)) {
            return $next($request);
        }
        if ($this->isCrawler($request) || ! $this->isMobile($request)) {
            return $next($request);
        }
        if ($request->user()) {
            return $next($request);
        }
        return redirect()->guest(route('login'));
    }

    protected function isCrawler(Request $request): bool
    {
        return (bool) preg_match(
            '/googlebot|bingbot|slurp|duckduckbot|baiduspider|yandexbot|facebookexternalhit|twitterbot|linkedinbot|pinterestbot|slackbot|telegrambot|whatsapp|redditbot|embedly|w3c_validator|developers\.google\.com/i',
            (string) $request->userAgent()
        );
    }

    protected function isMobile(Request $request): bool
    {
        $ua = strtolower((string) $request->userAgent());
        // Exclude tablets -> treat as "large"
        if (preg_match('/ipad|tablet|kindle|playbook|silk|nexus [79]|sm-t|gt-|sch-|hudl|smart-tv|googletv|appletv|crkey/i', $ua)) {
            return false;
        }
        return (bool) preg_match(
            '/mobile|iphone|ipod|android|blackberry|bb10|windows phone|iemobile|opera mini|webos|palm|symbian|fennec|midp|mobi|htc|ericsson|docomo|kddi|up\.browser|up\.link|lge|lg-|samsung|sch|sgh|siemens|softbank|sanyo|sharp|sprint|vodafone|wap|wml|zte|huawei|motorola|nokia|sonyericsson|panasonic|philips|sagem|tcl|vertu|virgin|vk|wapa|wapp|wapr|webc|windows ce|zune/i',
            $ua
        );
    }
}
```

### Modify `bootstrap/app.php` — register the alias
```php
$middleware->alias([
    'admin' => EnsureUserIsAdmin::class,
    'user.active' => EnsureUserIsActive::class,
    'require.login.on.mobile' => RequireLoginOnMobile::class,
]);
```

### Modify `config/auth.php` — add toggle (default on)
```php
'require_login_on_mobile' => env('REQUIRE_LOGIN_ON_MOBILE', true),
```

### Modify `routes/web.php` — wrap public routes in the gate
Keep `/login`, `/admin*`, and the `auth` group untouched:
```php
Route::livewire('/login', PhoneLogin::class)->name('login')->middleware('guest');

Route::middleware(['require.login.on.mobile'])->group(function () {
    Route::livewire('/privacy-policy', 'privacy-policy')->name('privacy-policy');
    Route::livewire('/terms-conditions', 'terms-conditions')->name('terms-conditions');
    Route::livewire('/', Home::class)->name('home');
    Route::livewire('/shop', Shop::class)->name('shop');
    Route::livewire('/search', Search::class)->name('search');
    Route::livewire('/categories', CategoriesIndex::class)->name('categories.index');
    Route::livewire('/product/{product:slug}', ProductDetail::class)->name('product.show');
    Route::livewire('/recipes', RecipesIndex::class)->name('recipes.index');
    Route::livewire('/recipes/{recipe:slug}', RecipeShow::class)->name('recipes.show');
    Route::livewire('/baskets', BasketsIndex::class)->name('baskets.index');
    Route::livewire('/baskets/{basket:slug}', BasketShow::class)->name('baskets.show');
});
// /admin* and the existing auth group stay as-is
```

### Modify `app/Livewire/Auth/PhoneLogin.php` (line 113) — honor intended URL
```php
$this->redirect(redirect()->intended(route('home')), navigate: true);
```

### Optional UX
When redirected, flash a message on the login page ("Please log in to continue browsing"). Add to the middleware `->with('info', ...)` and render in `phone-login.blade.php` (the page already shows `session('error')`; add an `info` style block). Low priority.

## 6. Tests (`tests/Feature/RequireLoginOnMobileTest.php`)
Using realistic UA strings and `withHeader('User-Agent', ...)`:
- mobile UA + **guest** → `GET /` → `302` to `login`.
- desktop UA + **guest** → `GET /` → `200`.
- mobile UA + **authed** (`actingAs`) → `GET /shop` → `200`.
- crawler UA (Googlebot) + **guest** → `GET /` → `200` (whitelisted).
- `REQUIRE_LOGIN_ON_MOBILE=false` + mobile guest → `200`.
- mobile guest → `GET /login` → `200` (not gated).
- mobile guest → `GET /privacy-policy` → `302` to `login` (your "all public pages" choice).
- mobile guest → `GET /cart` → `302` to `login` (existing auth gate still applies).

Also a small unit test asserting `isMobile()`/`isCrawler()` behave on sample UAs (tablet excluded, phone included, bot whitelisted).

Run: `php artisan test --compact --filter=RequireLoginOnMobile`. No `npm run build` needed (pure PHP).

## 7. Rollout
- Env toggle `REQUIRE_LOGIN_ON_MOBILE` (default `true`) lets you disable the rule instantly without code changes — ideal for the "likely not implemented" status; keep it `false` until you decide to enable.
- Document the env var in `.env.example`.

## 8. Risks / caveats
- **Legal pages on mobile:** gating privacy/terms behind login (per your choice) can conflict with app-store and privacy-law expectations that policies be publicly readable. If needed later, simply move the two legal routes out of the gated group — one-line change. Flagged, not blocking.
- **No-dependency UA detection** is a heuristic: rare/obscure UAs may misclassify, and some large phones in "desktop mode" won't be gated. Acceptable trade-off given the no-new-dependency constraint; the regex list is centralized in one method for easy tuning.
- **Livewire `wire:navigate`:** middleware runs on each server-side navigation request, so an authed session passes consistently; guests never mount gated components.
- **Crawlers:** whitelist covers major bots; add more patterns to `isCrawler()` as needed.

## 9. Out of scope
- Admin login/area (separate `auth:admin` guard, untouched).
- Checkout/cart/profile gating (already `auth`-protected; unchanged).
- Adding `mobiledetect/mobiledetectlib` (explicitly avoided per instruction).
