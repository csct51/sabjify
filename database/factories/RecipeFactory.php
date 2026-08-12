<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);
        $title = is_array($title) ? implode(' ', $title) : $title;

        return [
            'title' => Str::title($title),
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(),
            'image' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
