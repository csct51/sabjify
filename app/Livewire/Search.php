<?php

namespace App\Livewire;

use App\Models\Product;
use App\Support\ProductSearch;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.search')]
#[Title('Search')]
class Search extends Component
{
    #[Url(as: 'q', history: true)]
    public string $search = '';

    /** @var Collection<int, Product> */
    public Collection $items;

    public int $page = 1;

    public bool $hasMore = true;

    public bool $loadingMore = false;

    public bool $showSuggestions = true;

    public int $perPage = 12;

    public int $total = 0;

    public function mount(): void
    {
        $this->items = collect();
        $this->loadItems();
    }

    public function updatedSearch(): void
    {
        $this->showSuggestions = true;
        $this->resetItems();
    }

    public function selectSuggestion(int $id): void
    {
        $product = Product::find($id);

        if (! $product) {
            return;
        }

        $this->search = $product->name;
        $this->showSuggestions = false;
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
     * @return array{items: Collection<int, Product>, total: int, hasMore: bool}
     */
    private function runSearch(): array
    {
        return ProductSearch::search(
            term: $this->search,
            categorySlug: null,
            sort: 'name',
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

        $this->items = Collection::make(array_values($merged));
    }

    private function resetItems(): void
    {
        $this->page = 1;
        $this->items = collect();
        $this->hasMore = true;
        $this->loadItems();
    }

    #[Computed]
    public function totalResults(): int
    {
        return $this->total;
    }

    #[Computed]
    public function suggestions(): Collection
    {
        $term = trim($this->search);

        if (mb_strlen($term) < 2) {
            return collect();
        }

        $like = '%'.strtolower($term).'%';

        return Product::active()
            ->with('category')
            ->where(function ($query) use ($like, $term) {
                $query->where('name', 'like', '%'.$term.'%')
                    ->orWhereRaw('LOWER(alternate_names) LIKE ?', [$like]);
            })
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'slug', 'category_id']);
    }

    public function render(): View
    {
        return view('livewire.search');
    }
}
