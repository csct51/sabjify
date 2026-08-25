<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProductUnitPicker extends Component
{
    public ?int $productId = null;

    public ?int $selectedUnitId = null;

    public int $quantity = 1;

    public function open(int $productId): void
    {
        $this->productId = $productId;

        $product = $this->product();
        $firstInStock = $product?->units->firstWhere('in_stock', true);
        $this->selectedUnitId = $firstInStock === null
            ? $product?->defaultUnit()?->id
            : $firstInStock->id;
        $this->quantity = 1;
    }

    public function close(): void
    {
        $this->productId = null;
        $this->selectedUnitId = null;
        $this->quantity = 1;
    }

    public function selectUnit(int $unitId): void
    {
        $this->selectedUnitId = $unitId;
        $this->quantity = 1;
    }

    public function incrementQuantity(): void
    {
        $this->quantity++;
    }

    public function decrementQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    #[Computed]
    public function product(): ?Product
    {
        return $this->productId
            ? Product::with('units')->find($this->productId)
            : null;
    }

    #[Computed]
    public function selectedUnit(): ?ProductUnit
    {
        return $this->product()?->units->firstWhere('id', $this->selectedUnitId);
    }

    public function addToCart(): void
    {
        if (! auth('web')->check()) {
            $this->redirect(route('login'));

            return;
        }

        $product = $this->product();
        $unit = $this->selectedUnit();

        $inStock = $unit !== null ? $unit->in_stock : ($product?->inStock() ?? false);

        if (! $inStock) {
            $this->addError('stock', 'This product is out of stock.');

            return;
        }

        if (! $this->selectedUnitId || ! $this->selectedUnit()) {
            $this->addError('unit', 'Please select a size.');

            return;
        }

        $cartItem = auth('web')->user()->cartItems()->firstOrNew([
            'product_id' => $product->id,
            'product_unit_id' => $this->selectedUnitId,
        ]);
        $cartItem->quantity += $this->quantity;
        $cartItem->save();

        $this->close();

        $this->dispatch('cart-updated');
        $this->dispatch('product-unit-picker:close');
    }

    public function render(): View
    {
        return view('livewire.product-unit-picker');
    }
}
