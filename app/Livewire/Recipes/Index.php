<?php

namespace App\Livewire\Recipes;

use App\Models\Recipe;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.store')]
#[Title('Recipes')]
class Index extends Component
{
    /** @var Collection<int, Recipe> */
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

    private function loadItems(): void
    {
        $result = Recipe::active()
            ->withCount('products')
            ->orderBy('sort_order')
            ->latest()
            ->paginate($this->perPage, ['*'], 'page', $this->page);

        $this->hasMore = $result->hasMorePages();
        $this->items = Collection::make(array_merge($this->items->all(), $result->items()));
    }

    #[Computed]
    public function totalRecipes(): int
    {
        return Recipe::active()->count();
    }

    public function render(): View
    {
        return view('livewire.recipes.index');
    }
}
