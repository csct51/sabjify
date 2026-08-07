<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.store')]
#[Title('Categories')]
class Index extends Component
{
    use WithPagination;

    public function render(): View
    {
        $categories = Category::active()
            ->withCount('products')
            ->orderBy('sort_order')
            ->paginate(12);

        return view('livewire.categories.index', ['categories' => $categories]);
    }
}
