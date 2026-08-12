<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $sequence = 0;
        $sequence++;

        $subtotal = fake()->numberBetween(100, 2000);

        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
            'status' => fake()->randomElement(['pending', 'confirmed', 'packing', 'out_for_delivery', 'delivered']),
            'subtotal' => $subtotal,
            'delivery_fee' => $subtotal >= 499 ? 0 : 40,
            'discount' => 0,
            'total' => $subtotal,
            'payment_method' => fake()->randomElement(['cod', 'online']),
            'payment_status' => 'paid',
            'receiver_name' => fake()->name(),
            'receiver_phone' => '9'.str_pad((string) fake()->numberBetween(0, 999999999), 9, '0', STR_PAD_LEFT),
            'address_line' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->randomElement(['Maharashtra', 'Karnataka', 'Delhi', 'Tamil Nadu', 'Gujarat', 'Telangana']),
            'pincode' => fake()->numerify('######'),
            'label' => 'Home',
            'notes' => null,
        ];
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
