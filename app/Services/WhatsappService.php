<?php

namespace App\Services;

use App\Exceptions\WhatsappSendException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    /**
     * Send an OTP over WhatsApp via the AOC template API, using the
     * components/body-params template pattern ("your otp for sabjify
     * is {{1}}", single param = the code).
     *
     * No-op when no API key is configured (local/dev log-only mode).
     *
     * @throws WhatsappSendException
     */
    public function sendOtp(string $phone, string $code): void
    {
        $config = config('services.aoc.whatsapp');

        if (! is_array($config) || empty($config['key'])) {
            return;
        }

        $to = $this->normalizePhone($phone);

        try {
            $response = Http::timeout(8)->retry(1, 500)
                ->withHeaders(['apikey' => $config['key']])
                ->acceptJson()
                ->post($config['base_url'], [
                    'from' => $config['from'],
                    'campaignName' => $config['campaign'],
                    'to' => $to,
                    'templateName' => $config['template'],
                    'components' => [
                        'body' => [
                            'params' => [$code],
                        ],
                    ],
                    'type' => 'template',
                    'language' => ['code' => $config['language']],
                ]);

            if (! $response->successful()) {
                Log::warning('WhatsApp OTP rejected', ['phone' => $to, 'status' => $response->status(), 'body' => $response->body()]);

                throw new WhatsappSendException('WhatsApp gateway rejected the OTP request.');
            }

            Log::info('WhatsApp OTP accepted', ['phone' => $to, 'body' => $response->body()]);
        } catch (HttpClientException $e) {
            Log::warning('WhatsApp OTP transport failure', ['phone' => $to, 'error' => $e->getMessage()]);

            throw new WhatsappSendException('WhatsApp gateway unreachable.', previous: $e);
        }
    }

    /**
     * Normalize to E.164-ish international format (10-digit Indian → +91).
     */
    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (strlen($digits) === 10) {
            return '+91'.$digits;
        }

        return str_starts_with($phone, '+') ? $phone : '+'.$digits;
    }
}
