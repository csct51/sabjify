<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.store')]
class RecipeShow extends Component
{
    public Recipe $recipe;

    public ?string $cartMessage = null;

    public ?string $cartError = null;

    public function mount(Recipe $recipe): void
    {
        abort_unless($recipe->is_active, 404);

        $this->recipe = $recipe;
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function products(): Collection
    {
        return $this->recipe->products()->with('category', 'units')->get();
    }

    public function addAllToCart(): void
    {
        if (! auth('web')->check()) {
            $this->redirect(route('login'));

            return;
        }

        $added = 0;
        $skipped = [];

        foreach ($this->products() as $product) {
            if (! $product->inStock()) {
                $skipped[] = $product->name;

                continue;
            }

            $pivotUnitId = $product->pivot?->product_unit_id;

            $cartItem = auth('web')->user()->cartItems()->firstOrNew([
                'product_id' => $product->id,
                'product_unit_id' => $pivotUnitId,
            ]);
            $cartItem->recipe_id = $this->recipe->id;
            $cartItem->quantity++;
            $cartItem->save();
            $added++;
        }

        $this->dispatch('cart-updated');

        $this->cartMessage = $added > 0
            ? 'Added '.$added.' item'.($added > 1 ? 's' : '').' from this recipe to your cart.'
            : null;

        $this->cartError = $skipped !== []
            ? 'Out of stock: '.implode(', ', $skipped).'.'
            : null;
    }

    public function render(): View
    {
        return view('livewire.recipe-show');
    }
}
