<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ProductCard extends Component
{
    #[Locked]
    public Product $product;

    public ?int $unitId = null;

    public int $quantity = 1;

    public bool $inCart = false;

    public function mount(): void
    {
        if (auth('web')->check()) {
            $cartItem = auth('web')->user()
                ->cartItems()
                ->where('product_id', $this->product->id)
                ->first();

            if ($cartItem) {
                $this->inCart = true;
                $this->quantity = $cartItem->quantity;
                $this->unitId = $cartItem->product_unit_id;
            }
        }
    }

    public function addToCart(?int $unitId = null): void
    {
        if (! auth('web')->check()) {
            $this->redirect(route('login'));

            return;
        }

        $this->ensureStock();

        $unitId = $this->validatedUnitId($unitId ?? $this->unitId ?? $this->product->defaultUnit()?->id);
        $unit = $this->product->units->firstWhere('id', $unitId);

        $existing = (int) (auth('web')->user()->cartItems()
            ->where('product_id', $this->product->id)
            ->where('product_unit_id', $unitId)
            ->value('quantity') ?? 0);
        $packs = $this->product->sellablePacksFor($unit);

        if ($existing + 1 > $packs) {
            $this->addError('stock', $packs > 0 ? "Only {$packs} left." : 'This product is out of stock.');

            return;
        }

        $cartItem = auth('web')->user()->cartItems()->firstOrNew([
            'product_id' => $this->product->id,
            'product_unit_id' => $unitId,
        ]);
        $cartItem->quantity++;
        $cartItem->save();

        $this->inCart = true;
        $this->quantity = $cartItem->quantity;
        $this->unitId = $cartItem->product_unit_id;

        $this->dispatch('cart-updated');
    }

    public function increment(): void
    {
        $this->ensureStock();

        $unitId = $this->validatedUnitId($this->unitId);

        $cartItem = auth('web')->user()->cartItems()
            ->where('product_id', $this->product->id)
            ->where('product_unit_id', $unitId)
            ->firstOrFail();

        $unit = $this->product->units->firstWhere('id', $unitId);
        $packs = $this->product->sellablePacksFor($unit);

        if ($cartItem->quantity + 1 > $packs) {
            $this->addError('stock', $packs > 0 ? "Only {$packs} left." : 'This product is out of stock.');

            return;
        }

        $cartItem->increment('quantity');

        $this->quantity = $cartItem->quantity;

        $this->dispatch('cart-updated');
    }

    private function validatedUnitId(?int $unitId): ?int
    {
        if ($unitId === null) {
            return null;
        }

        if (! ProductUnit::where('product_id', $this->product->id)->where('id', $unitId)->exists()) {
            return $this->product->defaultUnit()?->id;
        }

        return $unitId;
    }

    public function decrement(): void
    {
        $cartItem = auth('web')->user()->cartItems()
            ->where('product_id', $this->product->id)
            ->where('product_unit_id', $this->unitId)
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
