<?php

namespace Database\Factories;

use App\Models\Basket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Basket>
 */
class BasketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $name = is_array($name) ? implode(' ', $name) : $name;

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'type' => fake()->randomElement([Basket::TYPE_WELLNESS, Basket::TYPE_SABJIFY]),
            'description' => fake()->sentence(),
            'image' => null,
            'price' => fake()->numberBetween(199, 1499),
            'mrp' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }

    public function wellness(): static
    {
        return $this->state(fn (): array => ['type' => Basket::TYPE_WELLNESS]);
    }

    public function sabjify(): static
    {
        return $this->state(fn (): array => ['type' => Basket::TYPE_SABJIFY]);
    }
}
