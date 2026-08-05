<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.store')]
#[Title('Fresh Fruits & Vegetables')]
class Home extends Component
{
    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::active()
            ->withCount('products')
            ->orderBy('sort_order')
            ->limit(6)
            ->get();
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function featuredProducts(): Collection
    {
        return Product::active()
            ->with('category')
            ->featured()
            ->orderBy('sort_order')
            ->limit(8)
            ->get();
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function newArrivals(): Collection
    {
        return Product::active()
            ->with('category')
            ->latest()
            ->limit(8)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.home');
    }
}
