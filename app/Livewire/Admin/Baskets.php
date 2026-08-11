<?php

namespace App\Livewire\Admin;

use App\Models\Basket;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Baskets')]
class Baskets extends Component
{
    public function toggleActive(Basket $basket): void
    {
        $basket->update(['is_active' => ! $basket->is_active]);

        $this->dispatch('toast', message: $basket->is_active ? "Basket \"{$basket->name}\" is now visible." : "Basket \"{$basket->name}\" is now hidden.");
    }

    public function delete(Basket $basket): void
    {
        $hasOrderReferences = $basket->orderItems()->exists();
        $hasCartReferences = $basket->cartItems()->exists();

        if ($hasOrderReferences || $hasCartReferences) {
            $this->dispatch('toast', type: 'error', message: $hasOrderReferences
                ? "Basket \"{$basket->name}\" cannot be deleted because it has been ordered. You can hide it instead."
                : "Basket \"{$basket->name}\" cannot be deleted because it is in someone's cart. You can hide it instead.");

            return;
        }

        $basket->delete();

        $this->dispatch('toast', message: "Basket \"{$basket->name}\" deleted.");
    }

    public function render(): View
    {
        $baskets = Basket::withCount('products')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return view('livewire.admin.baskets', ['baskets' => $baskets]);
    }
}
