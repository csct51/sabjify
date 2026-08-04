<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class StoreBottomNav extends Component
{
    #[Computed]
    public function cartCount(): int
    {
        if (! auth()->check()) {
            return 0;
        }

        return (int) auth()->user()->cartItems()->sum('quantity');
    }

    #[On('cart-updated')]
    public function refreshCart(): void
    {
        unset($this->cartCount);
    }

    public function render(): View
    {
        return view('livewire.store-bottom-nav');
    }
}
