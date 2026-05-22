<?php

declare(strict_types=1);

namespace Src\Domain\Drivers\DataTransferObjects;

/**
 * What the Drivers domain exposes about a matched driver — an id and how far
 * away they are.
 */
final readonly class NearestDriver
{
    public function __construct(
        public int $id,
        public float $distanceMeters,
    ) {}
}
