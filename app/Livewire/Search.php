<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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
     * @return Builder<Product>
     */
    private function query()
    {
        return Product::active()
            ->with(['category', 'units'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orderBy('name');
    }

    private function loadItems(): void
    {
        $result = $this->query()->paginate($this->perPage, ['*'], 'page', $this->page);

        $this->hasMore = $result->hasMorePages();
        $this->items = Collection::make(array_merge($this->items->all(), $result->items()));
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
        if (trim($this->search) === '') {
            return 0;
        }

        return $this->query()->count();
    }

    public function render(): View
    {
        return view('livewire.search');
    }
}
