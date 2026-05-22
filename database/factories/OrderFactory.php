<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Domain\Orders\Enums\OrderStatus;
use Src\Domain\Orders\Models\Entities\Order;

/**
 * @extends Factory<Order>
 */
final class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'reference' => fake()->uuid(),
            'status' => OrderStatus::Pending,
            'pickup_latitude' => fake()->randomFloat(7, 30.0000, 30.1000),
            'pickup_longitude' => fake()->randomFloat(7, 31.1800, 31.3000),
            'dropoff_latitude' => fake()->randomFloat(7, 30.0000, 30.1000),
            'dropoff_longitude' => fake()->randomFloat(7, 31.1800, 31.3000),
            'driver_id' => null,
            'assigned_at' => null,
        ];
    }
}
