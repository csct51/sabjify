<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Categories')]
class Categories extends Component
{
    public function toggleActive(Category $category): void
    {
        $category->update(['is_active' => ! $category->is_active]);

        $this->dispatch('toast', message: $category->is_active ? "Category \"{$category->name}\" is now visible." : "Category \"{$category->name}\" is now hidden.");
    }

    public function delete(Category $category): void
    {
        if ($category->products()->exists()) {
            $this->addError('delete', 'Cannot delete a category that has products. Move or delete its products first.');
            $this->dispatch('toast', message: 'Cannot delete a category that has products.', type: 'error');

            return;
        }

        $category->delete();

        $this->dispatch('toast', message: "Category \"{$category->name}\" deleted.");
    }

    public function render(): View
    {
        $categories = Category::withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.categories', ['categories' => $categories]);
    }
}
