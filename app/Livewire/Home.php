<?php

namespace App\Livewire;

use App\Models\Basket;
use App\Models\Category;
use App\Models\Product;
use App\Models\Recipe;
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
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function featuredProducts(): Collection
    {
        return Product::active()
            ->with(['category', 'units'])
            ->featured()
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return Collection<int, Recipe>
     */
    #[Computed]
    public function recipes(): Collection
    {
        return Recipe::active()
            ->withCount('products')
            ->orderBy('sort_order')
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, Basket>
     */
    #[Computed]
    public function wellnessBaskets(): Collection
    {
        return Basket::active()
            ->wellness()
            ->withCount('products')
            ->orderBy('sort_order')
            ->latest()
            ->limit(4)
            ->get();
    }

    /**
     * @return Collection<int, Basket>
     */
    #[Computed]
    public function sabjifyBaskets(): Collection
    {
        return Basket::active()
            ->sabjify()
            ->withCount('products')
            ->orderBy('sort_order')
            ->latest()
            ->limit(4)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.home');
    }
}
