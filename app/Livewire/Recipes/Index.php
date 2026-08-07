<?php

namespace App\Livewire\Recipes;

use App\Models\Recipe;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.store')]
#[Title('Recipes')]
class Index extends Component
{
    use WithPagination;

    public function render(): View
    {
        $recipes = Recipe::active()
            ->withCount('products')
            ->orderBy('sort_order')
            ->latest()
            ->paginate(12);

        return view('livewire.recipes.index', ['recipes' => $recipes]);
    }
}
