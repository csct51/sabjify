<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.store')]
#[Title('Shop')]
class Shop extends Component
{
    #[Url(as: 'q', history: false)]
    public string $search = '';

    #[Url(as: 'category', history: false)]
    public ?string $category = null;

    #[Url(as: 'sort', history: false)]
    public string $sort = 'latest';

    public bool $showFilters = false;

    /** @var BaseCollection<int, Product> */
    public BaseCollection $items;

    public int $page = 1;

    public bool $hasMore = true;

    public bool $loadingMore = false;

    public int $perPage = 12;

    public function mount(): void
    {
        $this->items = collect();
        $this->loadItems();
    }

    public function updatedSearch(): void
    {
        $this->resetItems();
    }

    public function updatedCategory(): void
    {
        $this->resetItems();
    }

    public function updatedSort(): void
    {
        $this->resetItems();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->category = null;
        $this->sort = 'latest';

        $this->resetItems();
    }

    public function loadMore(): void
    {
        if (! $this->hasMore || $this->loadingMore) {
            return;
        }

        $this->loadingMore = true;
        $this->page++;
        $this->loadItems();
        $this->loadingMore = false;
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

    #[Computed]
    public function totalProducts(): int
    {
        return Product::active()->count();
    }

    #[Computed]
    public function resultCount(): int
    {
        return $this->query()->count();
    }

    /**
     * @return Builder<Product>
     */
    private function query()
    {
        return Product::active()
            ->with(['category', 'units'])
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
            });
    }

    private function loadItems(): void
    {
        $result = $this->query()->paginate($this->perPage, ['*'], 'page', $this->page);

        $this->hasMore = $result->hasMorePages();
        $this->items = BaseCollection::make(array_merge($this->items->all(), $result->items()));
    }

    private function resetItems(): void
    {
        $this->page = 1;
        $this->items = collect();
        $this->hasMore = true;
        $this->loadItems();
    }

    public function render(): View
    {
        return view('livewire.shop');
    }
}
