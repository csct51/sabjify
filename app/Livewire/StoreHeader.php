<?php

namespace App\Livewire;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class StoreHeader extends Component
{
    #[Computed]
    public function cartCount(): int
    {
        if (! auth()->check()) {
            return 0;
        }

        return (int) auth()->user()->cartItems()->sum('quantity');
    }

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::active()
            ->orderBy('sort_order')
            ->get();
    }

    #[On('cart-updated')]
    public function refreshCart(): void
    {
        unset($this->cartCount);
    }

    public function logout(): void
    {
        auth()->logout();

        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(route('home'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.store-header');
    }
}
