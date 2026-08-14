<?php

namespace App\Livewire;

use App\Models\Basket;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BasketCard extends Component
{
    #[Locked]
    public Basket $basket;

    public int $quantity = 1;

    public bool $inCart = false;

    public function mount(): void
    {
        $this->basket->load('products:id,name');

        if (auth('web')->check()) {
            $cartItem = auth('web')->user()
                ->cartItems()
                ->where('basket_id', $this->basket->id)
                ->first();

            if ($cartItem) {
                $this->inCart = true;
                $this->quantity = $cartItem->quantity;
            }
        }
    }

    public function addToCart(): void
    {
        if (! auth('web')->check()) {
            $this->redirect(route('login'));

            return;
        }

        $cartItem = auth('web')->user()->cartItems()->firstOrNew(['basket_id' => $this->basket->id]);
        $cartItem->product_id = null;
        $cartItem->quantity++;
        $cartItem->save();

        $this->inCart = true;
        $this->quantity = $cartItem->quantity;

        $this->dispatch('cart-updated');
    }

    public function increment(): void
    {
        $cartItem = auth('web')->user()->cartItems()
            ->where('basket_id', $this->basket->id)
            ->firstOrFail();
        $cartItem->increment('quantity');

        $this->quantity = $cartItem->quantity;

        $this->dispatch('cart-updated');
    }

    public function decrement(): void
    {
        $cartItem = auth('web')->user()->cartItems()
            ->where('basket_id', $this->basket->id)
            ->firstOrFail();

        if ($cartItem->quantity <= 1) {
            $cartItem->delete();
            $this->inCart = false;
            $this->quantity = 1;

            $this->dispatch('cart-updated');

            return;
        }

        $cartItem->decrement('quantity');
        $this->quantity = $cartItem->quantity;

        $this->dispatch('cart-updated');
    }

    public function render(): View
    {
        return view('livewire.basket-card');
    }
}
