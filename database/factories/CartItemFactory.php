<?php

namespace Database\Factories;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 5),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (CartItem $cartItem) {
            if ($cartItem->product_id && ! $cartItem->product_unit_id) {
                $cartItem->product_unit_id = Product::findOrFail($cartItem->product_id)->defaultUnit()?->id;
            }
        });
    }

    public function ofUnit(int $unitId): static
    {
        return $this->state(fn (array $attributes) => [
            'product_unit_id' => $unitId,
        ]);
    }
}
