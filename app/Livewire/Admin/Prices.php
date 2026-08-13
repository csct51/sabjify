<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Prices')]
class Prices extends Component
{
    /** @var array<int, int|string> */
    public array $prices = [];

    /** @var array<int, int|string|null> */
    public array $mrps = [];

    public ?int $category = null;

    public string $search = '';

    public function mount(): void
    {
        $this->loadUnitValues();
    }

    public function updatedCategory(): void
    {
        $this->loadUnitValues();
    }

    public function updatedSearch(): void
    {
        $this->loadUnitValues();
    }

    private function loadUnitValues(): void
    {
        $this->prices = [];
        $this->mrps = [];

        foreach ($this->units as $unit) {
            $this->prices[$unit->id] = $unit->price;
            $this->mrps[$unit->id] = $unit->mrp;
        }
    }

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::active()->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, ProductUnit>
     */
    #[Computed]
    public function units(): Collection
    {
        return ProductUnit::query()
            ->with(['product.category'])
            ->when($this->category, fn ($query) => $query->whereHas('product', fn ($product) => $product->where('category_id', $this->category)))
            ->when($this->search !== '', fn ($query) => $query->whereHas('product', fn ($product) => $product->where('name', 'like', '%'.$this->search.'%')))
            ->orderBy('product_id')
            ->orderBy('sort_order')
            ->get();
    }

    public function toggleStock(Product $product): void
    {
        $product->update(['in_stock' => ! $product->in_stock]);

        $this->dispatch('toast', message: $product->fresh()->in_stock ? "\"{$product->name}\" is now in stock." : "\"{$product->name}\" is now out of stock.");
    }

    public function save(): void
    {
        foreach ($this->mrps as $unitId => $mrp) {
            $this->mrps[$unitId] = $mrp === '' ? null : $mrp;
        }

        $this->validate([
            'prices' => ['required', 'array'],
            'prices.*' => ['required', 'integer', 'min:1'],
            'mrps' => ['array'],
            'mrps.*' => ['nullable', 'integer', 'min:1'],
        ]);

        foreach ($this->prices as $unitId => $price) {
            ProductUnit::whereKey($unitId)->update([
                'price' => (int) $price,
                'mrp' => $this->mrps[$unitId] ?? null,
            ]);
        }

        $this->dispatch('toast', message: 'Prices updated.');
    }

    public function render(): View
    {
        return view('livewire.admin.prices');
    }
}
