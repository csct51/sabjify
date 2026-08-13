<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductUnit;
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

    public ?int $unitId = null;

    public int $quantity = 1;

    public bool $inCart = false;

    public function mount(): void
    {
        abort_if(! $this->product->is_active, 404);

        $this->unitId = $this->product->defaultUnit()?->id;

        $this->syncCartState();
    }

    public function selectUnit(int $unitId): void
    {
        $this->unitId = $unitId;
        $this->resetErrorBag();
        $this->syncCartState();
    }

    #[Computed]
    public function selectedUnit(): ?ProductUnit
    {
        return $this->product->units->firstWhere('id', $this->unitId);
    }

    private function syncCartState(): void
    {
        $this->inCart = false;
        $this->quantity = 1;

        if (! auth('web')->check() || ! $this->unitId) {
            return;
        }

        $cartItem = auth('web')->user()->cartItems()
            ->where('product_id', $this->product->id)
            ->where('product_unit_id', $this->unitId)
            ->first();

        if ($cartItem) {
            $this->inCart = true;
            $this->quantity = $cartItem->quantity;
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

    public function addToCart(): void
    {
        if (! auth('web')->check()) {
            $this->redirect(route('login'));

            return;
        }

        $this->ensureStock();

        $unitId = $this->unitId ?? $this->product->defaultUnit()?->id;

        $cartItem = auth('web')->user()->cartItems()->firstOrNew([
            'product_id' => $this->product->id,
            'product_unit_id' => $unitId,
        ]);
        $cartItem->quantity++;
        $cartItem->save();

        $this->unitId = $cartItem->product_unit_id;
        $this->syncCartState();

        $this->dispatch('cart-updated');
    }

    public function increment(): void
    {
        $this->ensureStock();

        $cartItem = auth('web')->user()->cartItems()
            ->where('product_id', $this->product->id)
            ->where('product_unit_id', $this->unitId)
            ->firstOrFail();
        $cartItem->increment('quantity');

        $this->syncCartState();

        $this->dispatch('cart-updated');
    }

    public function decrement(): void
    {
        $cartItem = auth('web')->user()->cartItems()
            ->where('product_id', $this->product->id)
            ->where('product_unit_id', $this->unitId)
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

    private function ensureStock(): void
    {
        if (! $this->product->inStock()) {
            $this->addError('stock', 'This product is out of stock.');

            return;
        }
    }

    public function render(): View
    {
        return view('livewire.product-detail');
    }
}
