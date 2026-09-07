<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Recipe;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class RecipeProduct extends Component
{
    #[Locked]
    public Recipe $recipe;

    #[Locked]
    public Product $product;

    public ?int $unitId = null;

    public int $quantity = 1;

    public bool $inCart = false;

    public function mount(): void
    {
        $this->unitId = $this->product->pivot?->product_unit_id;

        $this->syncCartState();
    }

    private function syncCartState(): void
    {
        if (! auth('web')->check()) {
            return;
        }

        $cartItem = auth('web')->user()
            ->cartItems()
            ->where('product_id', $this->product->id)
            ->where('recipe_id', $this->recipe->id)
            ->first();

        if ($cartItem) {
            $this->inCart = true;
            $this->quantity = $cartItem->quantity;
        } else {
            $this->inCart = false;
            $this->quantity = 1;
        }
    }

    #[On('cart-updated')]
    public function refreshCartState(): void
    {
        $this->syncCartState();
    }

    public function addToCart(): void
    {
        if (! auth('web')->check()) {
            $this->redirect(route('login'));

            return;
        }

        $unitId = $this->validatedUnitId($this->unitId);

        $unit = $this->product->units->firstWhere('id', $unitId);

        if (! ($unit?->in_stock ?? $this->product->inStock())) {
            $this->addError('stock', 'This product is out of stock.');

            return;
        }

        $existing = (int) (auth('web')->user()->cartItems()
            ->where('product_id', $this->product->id)
            ->where('product_unit_id', $unitId)
            ->sum('quantity') ?? 0);
        $packs = $this->product->sellablePacksFor($unit);

        if ($existing + 1 > $packs) {
            $this->addError('stock', $packs > 0 ? "Only {$packs} left." : 'This product is out of stock.');

            return;
        }

        $cartItem = auth('web')->user()->cartItems()->firstOrNew([
            'product_id' => $this->product->id,
            'recipe_id' => $this->recipe->id,
            'product_unit_id' => $unitId,
        ]);
        $cartItem->product_unit_id = $unitId;
        $cartItem->quantity++;
        $cartItem->save();

        $this->inCart = true;
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

    public function increment(): void
    {
        $cartItem = auth('web')->user()->cartItems()
            ->where('product_id', $this->product->id)
            ->where('recipe_id', $this->recipe->id)
            ->firstOrFail();

        $unit = $this->product->units->firstWhere('id', $cartItem->product_unit_id);
        $packs = $this->product->sellablePacksFor($unit);

        $existing = (int) (auth('web')->user()->cartItems()
            ->where('product_id', $this->product->id)
            ->where('product_unit_id', $cartItem->product_unit_id)
            ->sum('quantity') ?? 0);

        if ($existing + 1 > $packs) {
            $this->addError('stock', $packs > 0 ? "Only {$packs} left." : 'This product is out of stock.');

            return;
        }

        $cartItem->increment('quantity');

        $this->quantity = $cartItem->quantity;

        $this->dispatch('cart-updated');
    }

    public function decrement(): void
    {
        $cartItem = auth('web')->user()->cartItems()
            ->where('product_id', $this->product->id)
            ->where('recipe_id', $this->recipe->id)
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
        return view('livewire.recipe-product');
    }
}
