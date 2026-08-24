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
            'unit' => fake()->randomElement(['1 kg', '500 g', '1 pc', 'dozen', 'bunch', '250 g']),
            'price' => $price,
            'mrp' => fake()->boolean(70) ? (int) ($price * 1.25) : null,
            'image' => null,
            'is_active' => true,
            'is_featured' => fake()->boolean(30),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            $product->units()->create([
                'unit' => $product->unit,
                'price' => $product->price,
                'mrp' => $product->mrp,
                'in_stock' => fake()->boolean(80),
                'sort_order' => 0,
            ]);
        });
    }

    public function available(): static
    {
        return $this->afterCreating(function (Product $product) {
            $product->units()->update(['in_stock' => true]);
        });
    }

    public function outOfStock(): static
    {
        return $this->afterCreating(function (Product $product) {
            $product->units()->update(['in_stock' => false]);
        });
    }

    public function withUnits(int $count): static
    {
        return $this->afterCreating(function (Product $product) use ($count) {
            $basePrice = $product->price;

            $product->units()->where('sort_order', '>', 0)->delete();

            $pool = ['500 g', '250 g', '2 kg', '3 kg', '1 pc', 'dozen', 'bunch'];
            $available = array_values(array_filter($pool, fn (string $unit) => $unit !== $product->unit));

            for ($i = 1; $i < $count; $i++) {
                $product->units()->create([
                    'unit' => $available[$i % count($available)],
                    'price' => (int) ceil($basePrice * (1 + $i * 0.5)),
                    'mrp' => null,
                    'in_stock' => fake()->boolean(80),
                    'sort_order' => $i,
                ]);
            }
        });
    }
}
