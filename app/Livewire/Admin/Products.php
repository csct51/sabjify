<?php

namespace App\Livewire\Admin;

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
#[Title('Products')]
class Products extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $category = null;

    #[Url]
    public bool $lowStock = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function toggleActive(Product $product): void
    {
        $product->update(['is_active' => ! $product->is_active]);
    }

    public function toggleFeatured(Product $product): void
    {
        $product->update(['is_featured' => ! $product->is_featured]);
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::active()->orderBy('sort_order')->get();
    }

    public function render(): View
    {
        $products = Product::with('category')
            ->when($this->search, fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->category, fn ($query) => $query->where('category_id', $this->category))
            ->when($this->lowStock, fn ($query) => $query->where('stock', '<=', 10))
            ->orderByDesc('id')
            ->paginate(12);

        return view('livewire.admin.products', ['products' => $products]);
    }
}
