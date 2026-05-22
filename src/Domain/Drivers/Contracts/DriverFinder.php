<?php

declare(strict_types=1);

namespace Src\Domain\Drivers\Contracts;

use Src\Domain\Drivers\DataTransferObjects\NearestDriver;

/**
 * The Drivers domain's public gateway for locating a driver to assign.
 */
interface DriverFinder
{
    /**
     * Find the nearest assignable driver to a point, or null if none.
     */
    public function findNearestAvailable(
        float $latitude,
        float $longitude,
        ?float $radiusKm = null,
    ): ?NearestDriver;
}
