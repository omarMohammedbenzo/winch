<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Src\Domain\Drivers\Contracts\DriverFinder;
use Src\Domain\Drivers\DataTransferObjects\NearestDriver;
use Src\Domain\Drivers\Enums\DriverStatus;
use Src\Domain\Drivers\Models\Entities\Driver;
use Src\Domain\Orders\Contracts\OrderAssigner;
use Src\Domain\Orders\Enums\OrderStatus;
use Src\Domain\Orders\Events\OrderAssigned;
use Src\Domain\Orders\Exceptions\NoAvailableDriver;
use Src\Domain\Orders\Exceptions\OrderAlreadyAssigned;
use Src\Domain\Orders\Exceptions\OrderNotFound;
use Src\Domain\Orders\Models\Entities\Order;
use Tests\TestCase;

/**
 * Assignment logic, including the concurrency-safety paths.
 *
 * Only the geospatial DriverFinder is faked (it needs ST_Distance_Sphere); the
 * real transaction, row-lock claim, retry and exceptions are exercised.
 */
final class OrderAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private function fakeFinderReturns(?NearestDriver $driver): void
    {
        $this->mock(DriverFinder::class)
            ->shouldReceive('findNearestAvailable')
            ->andReturn($driver);
    }

    public function test_it_assigns_the_order_to_the_found_driver(): void
    {
        Event::fake([OrderAssigned::class]);

        $driver = Driver::factory()->create(['status' => DriverStatus::Available]);
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $this->fakeFinderReturns(new NearestDriver($driver->id, 123.4));

        $result = app(OrderAssigner::class)->assign($order->id);

        $this->assertSame($driver->id, $result->driverId);
        $this->assertSame(123.4, $result->distanceMeters);

        $order->refresh();
        $driver->refresh();
        $this->assertSame(OrderStatus::Assigned, $order->status);
        $this->assertSame($driver->id, $order->driver_id);
        $this->assertNotNull($order->assigned_at);
        $this->assertSame(DriverStatus::Busy, $driver->status);
        $this->assertSame($order->id, $driver->current_order_id);

        Event::assertDispatched(OrderAssigned::class);
    }

    public function test_it_fails_when_no_driver_is_found(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);
        $this->fakeFinderReturns(null);

        $this->expectException(NoAvailableDriver::class);
        app(OrderAssigner::class)->assign($order->id);
    }

    public function test_it_does_not_claim_a_driver_that_is_no_longer_assignable(): void
    {
        // The finder "found" this driver, but it is already busy — the claim must
        // reject it under the lock, and with no other candidate we get a failure.
        $busy = Driver::factory()->busy()->create();
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $this->fakeFinderReturns(new NearestDriver($busy->id, 50.0));

        $this->expectException(NoAvailableDriver::class);
        app(OrderAssigner::class)->assign($order->id);

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_it_rejects_an_order_that_is_not_pending(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Assigned]);

        $this->expectException(OrderAlreadyAssigned::class);
        app(OrderAssigner::class)->assign($order->id);
    }

    public function test_it_fails_for_a_missing_order(): void
    {
        $this->expectException(OrderNotFound::class);
        app(OrderAssigner::class)->assign(999_999);
    }
}
