<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->numberBetween(10, 600);

        $name = fake()->unique()->words(2, true);
        $name = is_array($name) ? implode(' ', $name) : $name;

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'unit' => fake()->randomElement(['kg', '500 g', '1 pc', 'dozen', 'bunch', '250 g']),
            'price' => $price,
            'mrp' => fake()->boolean(70) ? (int) ($price * 1.25) : null,
            'stock' => fake()->numberBetween(0, 100),
            'image' => null,
            'is_active' => true,
            'is_featured' => fake()->boolean(30),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }

    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'stock' => fake()->numberBetween(1, 100),
        ]);
    }
}
