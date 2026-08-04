<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Notifications\SendOtpNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

class OtpService
{
    public const OTP_LIFETIME_MINUTES = 5;

    public function send(string $phone): void
    {
        $this->invalidatePreviousCodes($phone);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'phone' => $phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(self::OTP_LIFETIME_MINUTES),
        ]);

        Notification::send(
            (new AnonymousNotifiable)->route('log', $phone),
            new SendOtpNotification($code),
        );
    }

    public function verify(string $phone, string $code): bool
    {
        $otp = OtpCode::where('phone', $phone)
            ->where('code', $code)
            ->latest()
            ->first();

        if (! $otp?->isValid()) {
            return false;
        }

        $otp->markAsUsed();

        return true;
    }

    private function invalidatePreviousCodes(string $phone): void
    {
        OtpCode::where('phone', $phone)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['expires_at' => now()]);
    }
}
