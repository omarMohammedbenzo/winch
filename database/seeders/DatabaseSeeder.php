<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Src\Domain\Drivers\Enums\DriverStatus;
use Src\Domain\Drivers\Models\Entities\Driver;
use Src\Domain\Orders\Enums\OrderStatus;
use Src\Domain\Orders\Models\Entities\Order;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        $available = Driver::factory()->count(20)->create();
        Driver::factory()->count(4)->offline()->create();

        $orders = Order::factory()->count(30)->create(); // pending

        // Turn 5 available drivers busy by assigning them a real order.
        $available->take(5)->each(function (Driver $driver) use ($orders): void {
            $order = $orders->shift();

            $order->update([
                'driver_id' => $driver->id,
                'status' => OrderStatus::Assigned,
                'assigned_at' => Carbon::now(),
            ]);

            $driver->update([
                'status' => DriverStatus::Busy,
                'current_order_id' => $order->id,
            ]);
        });
    }
}
