<?php

declare(strict_types=1);

namespace Src\Domain\Orders\Actions;

use Illuminate\Support\Facades\DB;
use Src\Domain\Drivers\Contracts\DriverClaimer;
use Src\Domain\Drivers\Contracts\DriverFinder;
use Src\Domain\Orders\Contracts\OrderAssigner;
use Src\Domain\Orders\DataTransferObjects\OrderAssignmentResult;
use Src\Domain\Orders\Enums\OrderStatus;
use Src\Domain\Orders\Events\OrderAssigned;
use Src\Domain\Orders\Exceptions\NoAvailableDriver;
use Src\Domain\Orders\Exceptions\OrderAlreadyAssigned;
use Src\Domain\Orders\Exceptions\OrderNotFound;
use Src\Domain\Orders\Models\Entities\Order;

/**
 * Assigns an order to the nearest available driver, safely under concurrency.
 *
 * Talks to the Drivers domain only through its contracts (find + claim), never
 * the Driver model. The whole operation runs in one transaction with row locks
 * so two simultaneous requests can't double-book a driver or an order.
 */
final class AssignOrder implements OrderAssigner
{
    /** How many nearby candidates to try before giving up under contention. */
    private const MAX_CLAIM_ATTEMPTS = 3;

    public function __construct(
        private readonly DriverFinder $finder,
        private readonly DriverClaimer $claimer,
    ) {}

    public function assign(int $orderId): OrderAssignmentResult
    {
        return DB::transaction(function () use ($orderId): OrderAssignmentResult {
            // Lock the order row first: serialises competing assigns of the same order.
            $order = Order::query()->whereKey($orderId)->lockForUpdate()->first();

            if ($order === null) {
                throw new OrderNotFound($orderId);
            }

            if (! $order->status->canBeAssigned()) {
                throw new OrderAlreadyAssigned($order->id, $order->status);
            }

            // Find then claim. If a candidate is taken mid-flight, the finder
            // re-runs and naturally skips them (they're now busy), so we just
            // try the next nearest a bounded number of times.
            $claimedDriverId = null;
            $distanceMeters = 0.0;

            for ($attempt = 0; $attempt < self::MAX_CLAIM_ATTEMPTS; $attempt++) {
                $nearest = $this->finder->findNearestAvailable(
                    $order->pickup_latitude,
                    $order->pickup_longitude,
                );

                if ($nearest === null) {
                    break; // nobody left to try
                }

                if ($this->claimer->claim($nearest->id, $order->id)) {
                    $claimedDriverId = $nearest->id;
                    $distanceMeters = $nearest->distanceMeters;
                    break;
                }
            }

            if ($claimedDriverId === null) {
                throw new NoAvailableDriver($order->id);
            }

            $order->driver_id = $claimedDriverId;
            $order->status = OrderStatus::Assigned;
            $order->assigned_at = now();
            $order->save();

            OrderAssigned::dispatch($order->id, $claimedDriverId);

            return new OrderAssignmentResult(
                orderId: $order->id,
                reference: $order->reference,
                driverId: $claimedDriverId,
                distanceMeters: $distanceMeters,
                assignedAt: $order->assigned_at->toIso8601String(),
            );
        });
    }
}
