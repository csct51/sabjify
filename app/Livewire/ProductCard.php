<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ProductCard extends Component
{
    #[Locked]
    public Product $product;

    public int $quantity = 1;

    public bool $inCart = false;

    public function mount(): void
    {
        if (auth()->check()) {
            $cartItem = auth()->user()
                ->cartItems()
                ->where('product_id', $this->product->id)
                ->first();

            if ($cartItem) {
                $this->inCart = true;
                $this->quantity = $cartItem->quantity;
            }
        }
    }

    public function addToCart(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        $this->ensureStock();

        $cartItem = auth()->user()->cartItems()->firstOrNew(['product_id' => $this->product->id]);
        $cartItem->quantity = min($cartItem->quantity + 1, $this->product->stock);
        $cartItem->save();

        $this->inCart = true;
        $this->quantity = $cartItem->quantity;

        $this->dispatch('cart-updated');
    }

    public function increment(): void
    {
        $this->ensureStock();

        $cartItem = auth()->user()->cartItems()->where('product_id', $this->product->id)->firstOrFail();
        $cartItem->quantity = min($cartItem->quantity + 1, $this->product->stock);
        $cartItem->save();

        $this->quantity = $cartItem->quantity;

        $this->dispatch('cart-updated');
    }

    public function decrement(): void
    {
        $cartItem = auth()->user()->cartItems()->where('product_id', $this->product->id)->firstOrFail();

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
        return view('livewire.product-card');
    }

    private function ensureStock(): void
    {
        if (! $this->product->inStock()) {
            $this->addError('stock', 'This product is out of stock.');

            return;
        }
    }
}
