<?php

declare(strict_types=1);

namespace Src\Domain\Orders\Contracts;

use Src\Domain\Orders\DataTransferObjects\OrderAssignmentResult;

/**
 * The Orders domain's public gateway for assigning an order to a driver.
 *
 * The presentation layer depends on this interface, not on the AssignOrder
 * action behind it.
 */
interface OrderAssigner
{
    /**
     * Assign the order to the nearest available driver.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException order missing
     * @throws \Src\Domain\Orders\Exceptions\OrderAlreadyAssigned order not pending
     * @throws \Src\Domain\Orders\Exceptions\NoAvailableDriver no driver claimable
     */
    public function assign(int $orderId): OrderAssignmentResult;
}
