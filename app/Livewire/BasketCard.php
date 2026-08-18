<?php

namespace App\Livewire;

use App\Models\Basket;
use App\Models\Product;
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
        $this->basket->load(['products:id,name,unit', 'products.units']);

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

    /**
     * @return array<int, array{name: string, unit: string}>
     */
    public function overlayItems(): array
    {
        return $this->basket->products
            ->map(function (Product $product): array {
                $unit = $product->units->firstWhere('id', $product->pivot?->product_unit_id)?->unit
                    ?? $product->units->first()?->unit
                    ?? $product->unit;

                return [
                    'name' => $product->name,
                    'unit' => $unit,
                ];
            })
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.basket-card');
    }
}
