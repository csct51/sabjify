<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.store')]
class ProductDetail extends Component
{
    #[Locked]
    public Product $product;

    public int $quantity = 1;

    public bool $inCart = false;

    public function mount(): void
    {
        abort_if(! $this->product->is_active, 404);

        if (auth()->check()) {
            $cartItem = auth()->user()->cartItems()->where('product_id', $this->product->id)->first();

            if ($cartItem) {
                $this->inCart = true;
                $this->quantity = $cartItem->quantity;
            }
        }
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function relatedProducts(): Collection
    {
        return Product::available()
            ->with('category')
            ->where('category_id', $this->product->category_id)
            ->whereKeyNot($this->product->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();
    }

    public function incrementQty(): void
    {
        $this->quantity = min($this->quantity + 1, $this->product->stock);
    }

    public function decrementQty(): void
    {
        $this->quantity = max($this->quantity - 1, 1);
    }

    public function addToCart(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        $this->validate(['quantity' => ['required', 'integer', 'min:1', 'max:'.$this->product->stock]]);

        $cartItem = auth()->user()->cartItems()->firstOrNew(['product_id' => $this->product->id]);
        $cartItem->quantity = min($cartItem->quantity + $this->quantity, $this->product->stock);
        $cartItem->save();

        $this->inCart = true;
        $this->quantity = $cartItem->quantity;

        $this->dispatch('cart-updated');
    }

    public function render(): View
    {
        return view('livewire.product-detail');
    }
}
