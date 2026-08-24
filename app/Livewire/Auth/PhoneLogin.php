<?php

namespace App\Livewire\Auth;

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
    public string $step = 'phone';

    public string $phone = '';

    public string $otp = '';

    public string $name = '';

    public bool $isNewUser = false;

    public bool $isInactive = false;

    public ?string $devOtp = null;

    public function sendOtp(): void
    {
        $this->validate(['phone' => ['required', 'regex:/^[6-9]\d{9}$/']]);

        $sendKey = 'otp-send:'.$this->phone;

        if (RateLimiter::tooManyAttempts($sendKey, 5)) {
            $this->addError('phone', 'Too many OTP requests. Please try again in '.RateLimiter::availableIn($sendKey).' seconds.');

            return;
        }

        app(OtpService::class)->send($this->phone);

        RateLimiter::hit($sendKey, 60);

        $this->isNewUser = ! User::where('phone', $this->phone)->exists();
        $this->isInactive = User::where('phone', $this->phone)->where('is_active', false)->exists();

        if ($this->isInactive) {
            $this->addError('phone', 'This account has been deactivated. Please contact support.');

            return;
        }

        $this->devOtp = app()->environment('local')
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
