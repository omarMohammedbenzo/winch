<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Src\Domain\Drivers\Contracts\DriverFinder;
use Src\Domain\Drivers\Enums\DriverStatus;
use Src\Domain\Drivers\Models\Entities\Driver;
use Tests\TestCase;

/**
 * Exercises the real ST_Distance_Sphere geo search. Skipped unless the test
 * connection is MySQL/MariaDB (sqlite has no ST_Distance_Sphere). Point
 * phpunit at a MySQL test database to run it.
 */
final class NearestDriverFinderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped('Geo search requires MySQL/MariaDB (ST_Distance_Sphere).');
        }
    }

    public function test_it_returns_the_nearest_assignable_driver(): void
    {
        // Reference point ~ central Cairo.
        $lat = 30.0444;
        $lng = 31.2357;

        $near = Driver::factory()->create(['status' => DriverStatus::Available, 'latitude' => 30.0460, 'longitude' => 31.2360]);
        Driver::factory()->create(['status' => DriverStatus::Available, 'latitude' => 30.0800, 'longitude' => 31.2700]); // farther
        Driver::factory()->busy()->create(['latitude' => 30.0445, 'longitude' => 31.2358]); // closest but busy

        $result = app(DriverFinder::class)->findNearestAvailable($lat, $lng);

        $this->assertNotNull($result);
        $this->assertSame($near->id, $result->id);
    }

    public function test_it_returns_null_when_nobody_is_in_range(): void
    {
        Driver::factory()->create(['status' => DriverStatus::Available, 'latitude' => 30.0460, 'longitude' => 31.2360]);

        $result = app(DriverFinder::class)->findNearestAvailable(30.0444, 31.2357, 0.05); // 50m radius

        $this->assertNull($result);
    }
}
