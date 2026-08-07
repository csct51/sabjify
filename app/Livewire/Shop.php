<?php

namespace App\Livewire;

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

#[Layout('layouts.store')]
#[Title('Shop')]
class Shop extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: false)]
    public string $search = '';

    #[Url(as: 'category', history: false)]
    public ?string $category = null;

    #[Url(as: 'sort', history: false)]
    public string $sort = 'latest';

    public bool $showFilters = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->category = null;
        $this->sort = 'latest';

        $this->resetPage();
    }

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::active()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->orderBy('sort_order')
            ->get();
    }

    public function render(): View
    {
        $products = Product::active()
            ->with('category')
            ->when($this->category, function ($query) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $this->category));
            })
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->when($this->sort, function ($query) {
                match ($this->sort) {
                    'price_low' => $query->orderBy('price'),
                    'price_high' => $query->orderByDesc('price'),
                    'popular' => $query->orderByDesc('sort_order'),
                    default => $query->latest(),
                };
            })
            ->paginate(12);

        return view('livewire.shop', ['products' => $products]);
    }
}
