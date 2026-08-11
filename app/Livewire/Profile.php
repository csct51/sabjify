<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.store')]
#[Title('My Profile')]
class Profile extends Component
{
    public function logout(): void
    {
        auth('web')->logout();

        session()->invalidate();
        session()->regenerateToken();

        $this->redirectRoute('login', navigate: true);
    }

    #[Computed]
    public function ordersCount(): int
    {
        return auth('web')->user()->orders()->count();
    }

    #[Computed]
    public function addressesCount(): int
    {
        return auth('web')->user()->addresses()->count();
    }

    public function render(): View
    {
        return view('livewire.profile');
    }
}
