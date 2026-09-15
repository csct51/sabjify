<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Support\ProductSearch;
use Illuminate\Contracts\View\View;
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

    public int $total = 0;

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
        return $this->total;
    }

    /**
     * @return array{items: Collection<int, Product>, total: int, hasMore: bool}
     */
    private function runSearch(): array
    {
        return ProductSearch::search(
            term: $this->search,
            categorySlug: $this->category,
            sort: $this->sort,
            page: $this->page,
            perPage: $this->perPage,
        );
    }

    private function loadItems(): void
    {
        $result = $this->runSearch();

        $this->total = $result['total'];
        $this->hasMore = $result['hasMore'];

        // Keyed merge: overlapping responses (rapid auto-load) collapse
        // instead of duplicating or dropping items.
        $merged = [];

        foreach (array_merge($this->items->all(), $result['items']->all()) as $item) {
            $merged[$item->getKey()] = $item;
        }

        $this->items = BaseCollection::make(array_values($merged));
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
