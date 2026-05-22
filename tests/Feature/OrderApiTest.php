<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Domain\Drivers\Contracts\DriverFinder;
use Src\Domain\Drivers\DataTransferObjects\NearestDriver;
use Src\Domain\Drivers\Enums\DriverStatus;
use Src\Domain\Drivers\Models\Entities\Driver;
use Src\Domain\Orders\Enums\OrderStatus;
use Src\Domain\Orders\Models\Entities\Order;
use Tests\TestCase;

final class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_index_defaults_to_active_only(): void
    {
        Order::factory()->count(3)->create(['status' => OrderStatus::Pending]);
        Order::factory()->count(2)->create(['status' => OrderStatus::Completed]);

        $this->getJson('/api/orders')
            ->assertOk()
            ->assertJsonPath('meta.total', 3);
    }

    public function test_orders_index_can_filter_by_a_specific_status(): void
    {
        Order::factory()->count(3)->create(['status' => OrderStatus::Pending]);
        Order::factory()->count(2)->create(['status' => OrderStatus::Completed]);

        $this->getJson('/api/orders?filter=completed')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }

    public function test_orders_index_rejects_an_invalid_filter(): void
    {
        $this->getJson('/api/orders?filter=banana')->assertStatus(422);
    }

    public function test_assign_endpoint_returns_200_with_the_assignment(): void
    {
        $driver = Driver::factory()->create(['status' => DriverStatus::Available]);
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $this->mock(DriverFinder::class)
            ->shouldReceive('findNearestAvailable')
            ->andReturn(new NearestDriver($driver->id, 200.0));

        $this->postJson("/api/orders/{$order->id}/assign")
            ->assertOk()
            ->assertJsonPath('data.driver_id', $driver->id);
    }

    public function test_assign_endpoint_maps_already_assigned_to_409(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Assigned]);

        $this->postJson("/api/orders/{$order->id}/assign")
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'order_already_assigned');
    }

    public function test_assign_endpoint_maps_no_driver_to_422(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $this->mock(DriverFinder::class)
            ->shouldReceive('findNearestAvailable')
            ->andReturn(null);

        $this->postJson("/api/orders/{$order->id}/assign")
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'no_available_driver');
    }

    public function test_assign_endpoint_maps_missing_order_to_404(): void
    {
        $this->postJson('/api/orders/999999/assign')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'order_not_found');
    }

    public function test_error_messages_are_localized_in_arabic(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Assigned]);

        $response = $this->postJson(
            "/api/orders/{$order->id}/assign",
            [],
            ['Accept-Language' => 'ar'],
        )->assertStatus(409);

        $this->assertStringContainsString('الطلب', $response->json('error.message'));
    }
}
