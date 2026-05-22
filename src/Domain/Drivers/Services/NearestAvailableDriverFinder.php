<?php

declare(strict_types=1);

namespace Src\Domain\Drivers\Services;

use Src\Domain\Drivers\Contracts\DriverFinder;
use Src\Domain\Drivers\DataTransferObjects\NearestDriver;
use Src\Domain\Drivers\Models\Entities\Driver;

/**
 * Default DriverFinder: nearest assignable driver via a two-step geo search.
 *
 *   1. Bounding-box pre-filter (indexed lat/lng) cuts millions of rows to a few.
 *   2. ST_Distance_Sphere() ranks the survivors by true great-circle metres.
 */
final class NearestAvailableDriverFinder implements DriverFinder
{
    /** Default search radius (km) when the caller doesn't specify one. */
    private const DEFAULT_RADIUS_KM = 10.0;

    public function findNearestAvailable(
        float $latitude,
        float $longitude,
        ?float $radiusKm = null,
    ): ?NearestDriver {
        $radius = $radiusKm ?? self::DEFAULT_RADIUS_KM;

        // ST_Distance_Sphere expects POINT(longitude, latitude) and returns metres.
        $driver = Driver::query()
            ->assignable()
            ->withinBoundingBox($latitude, $longitude, $radius)
            ->select('id')
            ->selectRaw(
                'ST_Distance_Sphere(POINT(longitude, latitude), POINT(?, ?)) as distance_meters',
                [$longitude, $latitude],
            )
            ->orderBy('distance_meters')
            ->first();

        if ($driver === null) {
            return null;
        }

        return new NearestDriver(
            id: (int) $driver->id,
            distanceMeters: (float) $driver->distance_meters,
        );
    }
}
