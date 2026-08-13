<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductUnit>
 */
class ProductUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->numberBetween(10, 600);

        return [
            'product_id' => Product::factory(),
            'unit' => fake()->randomElement(['kg', '500 g', '1 pc', 'dozen', 'bunch', '250 g', '125 g']),
            'price' => $price,
            'mrp' => fake()->boolean(70) ? (int) ($price * 1.25) : null,
            'sort_order' => 0,
        ];
    }
}
