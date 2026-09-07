<?php

namespace App\Livewire;

use App\Models\Basket;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.store')]
class BasketShow extends Component
{
    public Basket $basket;

    public int $quantity = 1;

    public bool $inCart = false;

    public ?string $cartError = null;

    public function mount(Basket $basket): void
    {
        abort_unless($basket->is_active, 404);

        $this->basket = $basket;

        $this->syncCartState();
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function products(): Collection
    {
        return $this->basket->products()->with('category', 'units')->get();
    }

    /**
     * @return Collection<int, Recipe>
     */
    #[Computed]
    public function recipes(): Collection
    {
        return $this->basket->recipes()->where('is_active', true)->orderBy('title')->get();
    }

    private function syncCartState(): void
    {
        $this->inCart = false;
        $this->quantity = 1;

        if (! auth('web')->check()) {
            return;
        }

        $cartItem = auth('web')->user()->cartItems()
            ->where('basket_id', $this->basket->id)
            ->first();

        if ($cartItem) {
            $this->inCart = true;
            $this->quantity = $cartItem->quantity;
        }
    }

    public function addToCart(): void
    {
        if (! auth('web')->check()) {
            $this->redirect(route('login'));

            return;
        }

        $existing = (int) (auth('web')->user()->cartItems()
            ->where('basket_id', $this->basket->id)
            ->value('quantity') ?? 0);
        $sellable = $this->basket->basketsSellable();

        if (! $this->basket->is_active || ! $this->basket->constituentsInStock() || $existing + 1 > $sellable) {
            $this->cartError = $sellable > 0 ? "Only {$sellable} baskets left." : 'This basket is out of stock.';

            return;
        }

        $cartItem = auth('web')->user()->cartItems()->firstOrNew(['basket_id' => $this->basket->id]);
        $cartItem->product_id = null;
        $cartItem->quantity = $cartItem->quantity + 1;
        $cartItem->save();

        $this->syncCartState();

        $this->dispatch('cart-updated');
    }

    public function increment(): void
    {
        $cartItem = auth('web')->user()->cartItems()
            ->where('basket_id', $this->basket->id)
            ->firstOrFail();

        $sellable = $this->basket->basketsSellable();

        if (! $this->basket->is_active || ! $this->basket->constituentsInStock() || $cartItem->quantity + 1 > $sellable) {
            $this->cartError = $sellable > 0 ? "Only {$sellable} baskets left." : 'This basket is out of stock.';

            return;
        }

        $cartItem->increment('quantity');

        $this->syncCartState();

        $this->dispatch('cart-updated');
    }

    public function decrement(): void
    {
        $cartItem = auth('web')->user()->cartItems()
            ->where('basket_id', $this->basket->id)
            ->firstOrFail();

        if ($cartItem->quantity <= 1) {
            $cartItem->delete();
            $this->syncCartState();

            $this->dispatch('cart-updated');

            return;
        }

        $cartItem->decrement('quantity');
        $this->syncCartState();

        $this->dispatch('cart-updated');
    }

    public function render(): View
    {
        return view('livewire.basket-show');
    }
}
