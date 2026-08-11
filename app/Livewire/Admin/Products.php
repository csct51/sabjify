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

#[Layout('layouts.admin')]
#[Title('Products')]
class Products extends Component
{
    public ?string $category = null;

    #[Url]
    public bool $lowStock = false;

    public function toggleActive(Product $product): void
    {
        $product->update(['is_active' => ! $product->is_active]);

        $this->dispatch('toast', message: $product->is_active ? "Product \"{$product->name}\" is now visible." : "Product \"{$product->name}\" is now hidden.");
    }

    public function toggleFeatured(Product $product): void
    {
        $product->update(['is_featured' => ! $product->is_featured]);

        $this->dispatch('toast', message: $product->is_featured ? "Product \"{$product->name}\" is now featured." : "Product \"{$product->name}\" is no longer featured.");
    }

    public function delete(Product $product): void
    {
        $product->delete();

        $this->dispatch('toast', message: "Product \"{$product->name}\" deleted.");
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
            ->when($this->category, fn ($query) => $query->where('category_id', $this->category))
            ->when($this->lowStock, fn ($query) => $query->where('stock', '<=', 10))
            ->orderByDesc('id')
            ->get();

        return view('livewire.admin.products', ['products' => $products]);
    }
}
