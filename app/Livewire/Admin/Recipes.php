<?php

namespace App\Livewire\Admin;

use App\Models\Recipe;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Recipes')]
class Recipes extends Component
{
    public function toggleActive(Recipe $recipe): void
    {
        $recipe->update(['is_active' => ! $recipe->is_active]);

        $this->dispatch('toast', message: $recipe->is_active ? "Recipe \"{$recipe->title}\" is now visible." : "Recipe \"{$recipe->title}\" is now hidden.");
    }

    public function delete(Recipe $recipe): void
    {
        $recipe->delete();

        $this->dispatch('toast', message: "Recipe \"{$recipe->title}\" deleted.");
    }

    public function render(): View
    {
        $recipes = Recipe::withCount('products')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return view('livewire.admin.recipes', ['recipes' => $recipes]);
    }
}
