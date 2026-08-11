<?php

namespace App\Livewire\Baskets;

use App\Models\Basket;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.store')]
#[Title('Baskets')]
class Index extends Component
{
    public function render(): View
    {
        return view('livewire.baskets.index', [
            'wellnessBaskets' => Basket::active()->wellness()->withCount('products')->orderBy('sort_order')->latest()->get(),
            'sabjifyBaskets' => Basket::active()->sabjify()->withCount('products')->orderBy('sort_order')->latest()->get(),
        ]);
    }
}
