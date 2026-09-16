<?php

namespace App\Livewire\Auth;

use App\Exceptions\WhatsappSendException;
use App\Models\OtpCode;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Login with Mobile')]
class PhoneLogin extends Component
{
    /**
     * Temporary debug bypass so the seller (phone 7869815580) can log in
     * with the fixed OTP 147258 during third-party setup, without a valid
     * WhatsApp-sent code. REMOVE after setup — see
     * .opencode/plans/otp-debug-bypass.md for the revert recipe.
     */
    private const DEBUG_BYPASS_PHONE = '7869815580';

    private const DEBUG_BYPASS_OTP = '147258';

    public string $step = 'phone';

    public string $phone = '';

    public string $otp = '';

    public string $name = '';

    public bool $isNewUser = false;

    public bool $isInactive = false;

    public ?string $devOtp = null;

    private function isDebugBypassPhone(): bool
    {
        return $this->phone === self::DEBUG_BYPASS_PHONE;
    }

    private function isDebugBypass(): bool
    {
        return $this->isDebugBypassPhone() && $this->otp === self::DEBUG_BYPASS_OTP;
    }

    public function sendOtp(): void
    {
        $this->validate(['phone' => ['required', 'regex:/^[6-9]\d{9}$/']]);

        if ($this->isDebugBypassPhone()) {
            $this->isNewUser = ! User::where('phone', $this->phone)->exists();
            $this->isInactive = User::where('phone', $this->phone)->where('is_active', false)->exists();

            if ($this->isInactive) {
                $this->addError('phone', 'This account has been deactivated. Please contact support.');

                return;
            }

            $this->otp = '';
            $this->step = 'otp';

            return;
        }

        $sendKey = 'otp-send:'.$this->phone;

        if (RateLimiter::tooManyAttempts($sendKey, 5)) {
            $this->addError('phone', 'Too many OTP requests. Please try again in '.RateLimiter::availableIn($sendKey).' seconds.');

            return;
        }

        try {
            app(OtpService::class)->send($this->phone);
        } catch (WhatsappSendException $e) {
            $this->addError('phone', 'Could not send the OTP on WhatsApp. Please try again.');

            return;
        }

        RateLimiter::hit($sendKey, 60);

        $this->isNewUser = ! User::where('phone', $this->phone)->exists();
        $this->isInactive = User::where('phone', $this->phone)->where('is_active', false)->exists();

        if ($this->isInactive) {
            $this->addError('phone', 'This account has been deactivated. Please contact support.');

            return;
        }

        // Dev hint only when WhatsApp is not sending for real: with a key
        // configured the code travels by message and must never render.
        $this->devOtp = $this->showsDevOtp()
            ? OtpCode::where('phone', $this->phone)->latest()->value('code')
            : null;

        $this->otp = '';
        $this->step = 'otp';
    }

    public function verifyOtp(): void
    {
        $rules = [
            'otp' => ['required', 'string', 'size:6'],
        ];

        if ($this->isNewUser) {
            $rules['name'] = ['required', 'string', 'max:100'];
        }

        $this->validate($rules);

        $verifyKey = 'otp-verify:'.$this->phone;

        if (! $this->isDebugBypass()) {
            if (RateLimiter::tooManyAttempts($verifyKey, 5)) {
                app(OtpService::class)->invalidateCodes($this->phone);
                $this->addError('otp', 'Too many incorrect attempts. Please request a new OTP.');

                return;
            }

            $otpService = app(OtpService::class);

            if (! $otpService->verify($this->phone, $this->otp)) {
                RateLimiter::hit($verifyKey, 60);
                $this->addError('otp', 'Invalid or expired OTP. Please try again.');

                return;
            }

            RateLimiter::clear($verifyKey);
        }

        $user = User::where('phone', $this->phone)->first();

        if (! $user) {
            $user = User::create([
                'name' => $this->name,
                'email' => null,
                'phone' => $this->phone,
                'password' => Str::random(32),
            ]);
        }

        auth('web')->login($user, true);

        session()->regenerate();

        $this->redirect(route('home'), navigate: true);
    }

    public function resendOtp(): void
    {
        $this->sendOtp();
    }

    public function showsDevOtp(): bool
    {
        return app()->environment('local') && empty(config('services.aoc.whatsapp.key'));
    }

    public function goBack(): void
    {
        $this->step = 'phone';
        $this->otp = '';
        $this->name = '';
        $this->isNewUser = false;
    }

    public function render(): View
    {
        return view('livewire.auth.phone-login');
    }
}
