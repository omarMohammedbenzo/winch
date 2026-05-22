<?php

declare(strict_types=1);

namespace Src\Domain\Orders\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired once an order has been successfully assigned to a driver.
 *
 * Nothing listens yet, but the seam is here: notifications, real-time pushes
 * to the dispatcher screen, analytics, etc. can subscribe without changing the
 * assignment logic. Carries ids only — listeners re-load what they need.
 */
final class OrderAssigned
{
    use Dispatchable;

    public function __construct(
        public readonly int $orderId,
        public readonly int $driverId,
    ) {}
}
