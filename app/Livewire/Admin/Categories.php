<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Categories')]
class Categories extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleActive(Category $category): void
    {
        $category->update(['is_active' => ! $category->is_active]);
    }

    public function delete(Category $category): void
    {
        if ($category->products()->exists()) {
            $this->addError('delete', 'Cannot delete a category that has products. Move or delete its products first.');

            return;
        }

        $category->delete();
    }

    public function render(): View
    {
        $categories = Category::withCount('products')
            ->when($this->search, fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.admin.categories', ['categories' => $categories]);
    }
}
