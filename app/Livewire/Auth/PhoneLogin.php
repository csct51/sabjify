<?php

namespace App\Livewire\Auth;

use App\Models\OtpCode;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Contracts\View\View;
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

        app(OtpService::class)->send($this->phone);

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

        $otpService = app(OtpService::class);

        if (! $otpService->verify($this->phone, $this->otp)) {
            $this->addError('otp', 'Invalid or expired OTP. Please try again.');

            return;
        }

        $user = User::where('phone', $this->phone)->first();

        if (! $user) {
            $user = User::create([
                'name' => $this->name,
                'email' => null,
                'phone' => $this->phone,
                'role' => User::ROLE_CUSTOMER,
                'password' => Str::random(32),
            ]);
        }

        auth()->login($user, true);

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
