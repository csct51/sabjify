# WhatsApp OTP Plan (AOC API)

## Goal
Send login OTPs over WhatsApp via api.aoc-portal.com (POST /v1/whatsapp,
apikey header, template payload) instead of log-only. Secrets env-only;
no key without WhatsApp (dev behavior unchanged).

## Decisions (owner-locked, do NOT forget)
- Pasted API key is COMPROMISED (old project + chat) — owner must rotate in
  AOC dashboard. Only the fresh key goes to server .env. Never in code.
- campaignName "api-test" kept as env default; owner confirms prod value.
- Failure UX: user-facing "couldn't send, retry" (no silent lockout).
- Sync send inside OtpService (OTP can't wait on a queue worker).
- No new packages — Laravel HTTP client only.

## Implementation
1. config/services.php `aoc.whatsapp` (base_url, key, from, campaign,
   template, language — all env with old-function defaults).
2. `App\Exceptions\WhatsappSendException` + `App\Services\WhatsappService`
   (10-digit→+91 normalize, timeout 8 + retry 1, apikey header, throws on
   transport/non-2xx, logs response; no-op when key missing).
3. `OtpService::send()`: create row (unchanged) → log notification
   (unchanged, dev path) → WhatsappService when key configured; failure
   throws to PhoneLogin which shows retry error without advancing step.
4. Tests (Http::fake): payload shape, failure UX, no-key silence. Suite+pint.
5. Deploy: owner adds AOC_WHATSAPP_KEY (+ optional overrides) to server
   .env only, then one real-number end-to-end.

## Status: EXECUTED 2026-09-08 (uncommitted; no secrets in repo)
