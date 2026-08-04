<?php

namespace App\Livewire;

use App\Models\CartItem;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.store')]
#[Title('My Cart')]
class Cart extends Component
{
    public function increment(CartItem $item): void
    {
        if (! $item->product || ! $item->product->inStock()) {
            return;
        }

        if ($item->quantity >= $item->product->stock) {
            $this->addError('quantity', 'Stock limit reached for '.$item->product->name.'.');

            return;
        }

        $item->increment('quantity');

        $this->dispatch('cart-updated');
    }

    public function decrement(CartItem $item): void
    {
        if ($item->quantity <= 1) {
            $this->remove($item);

            return;
        }

        $item->decrement('quantity');

        $this->dispatch('cart-updated');
    }

    public function remove(CartItem $item): void
    {
        $this->authorize('delete', $item);

        $item->delete();

        $this->dispatch('cart-updated');
    }

    /**
     * @return Collection<int, CartItem>
     */
    #[Computed]
    public function cartItems(): Collection
    {
        return auth()->user()->cartItems()
            ->with('product.category')
            ->latest()
            ->get();
    }

    #[Computed]
    public function subtotal(): int
    {
        return $this->cartItems()->sum(fn (CartItem $item) => $item->product ? $item->product->price * $item->quantity : 0);
    }

    #[Computed]
    public function deliveryFee(): int
    {
        $subtotal = $this->subtotal();

        if ($subtotal === 0) {
            return 0;
        }

        return $subtotal >= config('mart.free_delivery_threshold') ? 0 : (int) config('mart.delivery_fee');
    }

    #[Computed]
    public function total(): int
    {
        return $this->subtotal() + $this->deliveryFee();
    }

    public function render(): View
    {
        return view('livewire.cart');
    }
}
