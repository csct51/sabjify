<?php

namespace Database\Factories;

use App\Models\DeliveryLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryLocation>
 */
class DeliveryLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city().' Hub',
            'latitude' => fake()->latitude(18.9, 19.3),
            'longitude' => fake()->longitude(72.8, 72.9),
            'radius_km' => fake()->numberBetween(5, 15),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
