<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Domain\Drivers\Enums\DriverStatus;
use Src\Domain\Drivers\Models\Entities\Driver;
use Src\Domain\Orders\Enums\OrderStatus;
use Src\Domain\Orders\Models\Entities\Order;
use Tests\TestCase;

final class DriverApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_searches_drivers_by_name(): void
    {
        Driver::factory()->create(['name' => 'Ahmed Hassan']);
        Driver::factory()->create(['name' => 'Sara Ali']);

        $this->getJson('/api/drivers?search=Ahmed')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Ahmed Hassan');
    }

    public function test_it_searches_drivers_by_phone(): void
    {
        Driver::factory()->create(['phone' => '+201001234567']);
        Driver::factory()->create(['phone' => '+201009998888']);

        $this->getJson('/api/drivers?search=1234567')
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_it_filters_drivers_by_status(): void
    {
        Driver::factory()->count(2)->create(['status' => DriverStatus::Available]);
        Driver::factory()->count(3)->offline()->create();

        $this->getJson('/api/drivers?status=offline')
            ->assertOk()
            ->assertJsonPath('meta.total', 3);
    }

    public function test_driver_orders_are_paginated_and_filterable(): void
    {
        $driver = Driver::factory()->create();
        Order::factory()->count(4)->create(['driver_id' => $driver->id, 'status' => OrderStatus::Assigned]);
        Order::factory()->count(2)->create(['driver_id' => $driver->id, 'status' => OrderStatus::Completed]);

        $this->getJson("/api/drivers/{$driver->id}/orders?per_page=3")
            ->assertOk()
            ->assertJsonPath('meta.total', 6)
            ->assertJsonPath('meta.per_page', 3)
            ->assertJsonCount(3, 'data');

        $this->getJson("/api/drivers/{$driver->id}/orders?status=completed")
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }

    public function test_unknown_driver_returns_404(): void
    {
        $this->getJson('/api/drivers/999999/orders')->assertStatus(404);
    }

    public function test_invalid_order_status_filter_is_rejected(): void
    {
        $driver = Driver::factory()->create();

        $this->getJson("/api/drivers/{$driver->id}/orders?status=banana")
            ->assertStatus(422);
    }
}
