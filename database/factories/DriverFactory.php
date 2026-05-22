<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Domain\Drivers\Enums\DriverStatus;
use Src\Domain\Drivers\Models\Entities\Driver;

/**
 * @extends Factory<Driver>
 */
final class DriverFactory extends Factory
{
    protected $model = Driver::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->unique()->numerify('+20100#######'),
            'status' => DriverStatus::Available,
            'latitude' => fake()->randomFloat(7, 30.0000, 30.1000),
            'longitude' => fake()->randomFloat(7, 31.1800, 31.3000),
            'current_order_id' => null,
        ];
    }

    public function busy(): static
    {
        return $this->state(fn () => ['status' => DriverStatus::Busy]);
    }

    public function offline(): static
    {
        return $this->state(fn () => ['status' => DriverStatus::Offline]);
    }
}
