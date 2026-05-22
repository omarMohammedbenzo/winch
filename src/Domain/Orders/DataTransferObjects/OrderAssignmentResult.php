<?php

declare(strict_types=1);

namespace Src\Domain\Orders\DataTransferObjects;

/**
 * Outcome of a successful assignment, returned by the Orders domain to the
 * presentation layer. Includes the (transient) match distance so the API can
 * report how far the assigned driver was, without persisting it.
 */
final readonly class OrderAssignmentResult
{
    public function __construct(
        public int $orderId,
        public string $reference,
        public int $driverId,
        public float $distanceMeters,
        public string $assignedAt,
    ) {}
}
