<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Stock Quantity')]
class Stock extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $category = null;

    #[Url]
    public string $stockFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingStockFilter(): void
    {
        $this->resetPage();
    }

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::active()->orderBy('sort_order')->get();
    }

    public function toggleStock(Product $product): void
    {
        $hasStock = $product->units()->where('in_stock', true)->exists();
        $new = ! $hasStock;
        $product->units()->update(['in_stock' => $new]);
        $this->dispatch('toast', message: $new ? "\"{$product->name}\" is now in stock." : "\"{$product->name}\" is now out of stock.");
    }

    #[Computed]
    public function summary(): array
    {
        return [
            'total' => Product::count(),
            'in' => Product::where('current_stock', '>', 0)->count(),
            'out' => Product::where('current_stock', 0)->count(),
            'low' => Product::lowStock()->count(),
        ];
    }

    public function render(): View
    {
        $products = Product::query()
            ->with(['category'])
            ->when($this->search !== '', function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('alternate_names', 'like', '%'.$this->search.'%')
                    ->orWhereHas('category', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'));
            })
            ->when($this->category, fn ($q) => $q->where('category_id', $this->category))
            ->when($this->stockFilter === 'out', fn ($q) => $q->where('current_stock', 0))
            ->when($this->stockFilter === 'low', fn ($q) => $q->lowStock())
            ->when($this->stockFilter === 'in', fn ($q) => $q->where('current_stock', '>', 0))
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.admin.reports.stock', [
            'products' => $products,
        ]);
    }
}
