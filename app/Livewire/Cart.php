<?php

namespace App\Livewire;

use App\Models\Basket;
use App\Models\CartItem;
use App\Models\Recipe;
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
        if ($item->basket) {
            $item->increment('quantity');
            $this->dispatch('cart-updated');

            return;
        }

        if (! $item->product || ! $item->product->inStock()) {
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
        return auth('web')->user()->cartItems()
            ->with('product.category', 'productUnit', 'recipe', 'basket.products.units')
            ->latest()
            ->get();
    }

    #[Computed]
    public function subtotal(): int
    {
        return $this->cartItems()->sum(fn (CartItem $item) => $item->total());
    }

    /**
     * Group cart items by the recipe or basket they came from, keeping
     * standalone items in their own single-item groups.
     *
     * @return array<int|string, array{recipe: Recipe|null, basket: Basket|null, items: array<int, CartItem>}>
     */
    #[Computed]
    public function cartGroups(): array
    {
        /** @var array<int|string, array{recipe: Recipe|null, basket: Basket|null, items: array<int, CartItem>}> $grouped */
        $grouped = [];

        foreach ($this->cartItems() as $item) {
            $key = $item->recipe_id
                ? 'recipe-'.$item->recipe_id
                : ($item->basket_id ? 'basket-'.$item->basket_id : 'item-'.$item->id);

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'recipe' => $item->recipe,
                    'basket' => $item->basket,
                    'items' => [],
                ];
            }

            $grouped[$key]['items'][] = $item;
        }

        return $grouped;
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

    #[Computed]
    public function belowMinimum(): bool
    {
        $minimum = (int) config('mart.minimum_order_amount');

        return $minimum > 0 && $this->subtotal() < $minimum;
    }

    /**
     * @return Collection<int, CartItem>
     */
    #[Computed]
    public function outOfStockItems(): Collection
    {
        return $this->cartItems()
            ->filter(fn (CartItem $item) => $item->product && ! $item->product->inStock())
            ->values();
    }

    public function render(): View
    {
        return view('livewire.cart');
    }
}
