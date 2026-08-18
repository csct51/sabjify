<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
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
            'label' => fake()->randomElement(['Home', 'Work', 'Other']),
            'receiver_name' => fake()->name(),
            'receiver_phone' => '9'.str_pad((string) fake()->unique()->numberBetween(0, 999999999), 9, '0', STR_PAD_LEFT),
            'address_line' => fake()->streetAddress(),
            'landmark' => fake()->randomElement([null, 'Near Market', 'Opposite Park']),
            'city' => fake()->city(),
            'state' => fake()->randomElement(['Maharashtra', 'Karnataka', 'Delhi', 'Tamil Nadu', 'Gujarat', 'Telangana']),
            'pincode' => fake()->numerify('######'),
            'is_default' => false,
        ];
    }

    public function withLocation(float $latitude, float $longitude): static
    {
        return $this->state(fn (): array => [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }
}
