<?php

namespace App\Livewire;

use App\Models\Basket;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.store')]
class BasketShow extends Component
{
    public Basket $basket;

    public ?string $cartMessage = null;

    public ?string $cartError = null;

    public function mount(Basket $basket): void
    {
        abort_unless($basket->is_active, 404);

        $this->basket = $basket;
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function products(): Collection
    {
        return $this->basket->products()->with('category')->get();
    }

    public function addToCart(): void
    {
        if (! auth('web')->check()) {
            $this->redirect(route('login'));

            return;
        }

        $cartItem = auth('web')->user()->cartItems()->firstOrNew(['basket_id' => $this->basket->id]);
        $cartItem->product_id = null;
        $cartItem->quantity = $cartItem->quantity + 1;
        $cartItem->save();

        $this->dispatch('cart-updated');

        $this->cartMessage = "\"{$this->basket->name}\" added to your cart.";
    }

    public function render(): View
    {
        return view('livewire.basket-show');
    }
}
