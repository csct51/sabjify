<?php

namespace App\Livewire\Admin\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Admin Login')]
class AdminLogin extends Component
{
    public string $username = '';

    public string $password = '';

    public function login(): void
    {
        $this->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $key = 'admin-login:'.strtolower($this->username).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('username', 'Too many login attempts. Try again in '.RateLimiter::availableIn($key).' seconds.');

            return;
        }

        if (! Auth::guard('admin')->attempt($this->only('username', 'password'), true)) {
            RateLimiter::hit($key, 60);
            $this->addError('username', 'These credentials do not match our records.');

            return;
        }

        RateLimiter::clear($key);

        session()->regenerate();

        $this->redirect(route('admin.dashboard'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.auth.admin-login');
    }
}
