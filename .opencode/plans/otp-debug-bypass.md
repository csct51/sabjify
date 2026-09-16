# OTP Debug Bypass — Temporary Seller Login

**Added:** 2026-09-16
**Status:** temporary — remove after third-party setup is complete

## Purpose

The app uses OTP-only authentication (WhatsApp via AOC, codes verified against an
`otp_codes` row in the DB/session). During third-party integration setup we need to
log into the seller account without a working WhatsApp OTP, so this two-part combo
logs the account in **without verifying any OTP**.

## Behaviour

- Phone `7869815580` + OTP `147258` → skips rate limiting and `OtpService::verify()`,
  then logs into the existing account for that number via the normal find-or-create
  + `auth('web')->login()` path.
- Same phone + any other OTP → normal verification path → rejected.
- The bypass OTP used on any other phone number → ignored (normal path).

## Files touched

- `app/Livewire/Auth/PhoneLogin.php`
- `tests/Feature/OtpDebugBypassTest.php` (4 tests)
- this file

## Where the bypass lives (line numbers as of 2026-09-16)

1. Const `DEBUG_BYPASS_PHONE` + `DEBUG_BYPASS_OTP` (around lines 26–28) with an
   explanatory PHPDoc above them.
2. Helpers `isDebugBypassPhone()` (~44–47) and `isDebugBypass()` (~49–52).
3. `sendOtp()` early-return branch for the bypass phone (~58–72): skips
   `OtpService::send()` (no WhatsApp call, no `OtpCode` row), still checks
   active/inactive, jumps straight to `step = 'otp'`.
4. `verifyOtp()` wrapper `if (! $this->isDebugBypass()) { ... }` (~125–143):
   the rate-limit + `OtpService::verify()` block only runs for non-bypass logins.

## Revert recipe (mechanical)

1. **`PhoneLogin.php`** — delete the PHPDoc + `DEBUG_BYPASS_*` const block (~20–28).
2. **`PhoneLogin.php`** — delete helpers `isDebugBypassPhone()` and `isDebugBypass()` (~44–52).
3. **`PhoneLogin.php`** — in `sendOtp()`, delete the `if ($this->isDebugBypassPhone()) { ... return; }`
   block (~58–72). The method reverts to the original flow.
4. **`PhoneLogin.php`** — in `verifyOtp()`, unwrap `if (! $this->isDebugBypass()) { ... }`
   (~125–143) back to its inner body (no wrapper).
5. Delete `tests/Feature/OtpDebugBypassTest.php`.
6. Delete this file.

After removal: run `vendor/bin/pint --dirty --format agent`, then the auth test files
(`AuthTest`, `WhatsappOtpTest`), then the full suite.

## Guardrail

This is a known backdoor for one account — no OTP verification. It must be removed
before the app goes live to the public.