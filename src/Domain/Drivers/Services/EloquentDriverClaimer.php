<?php

declare(strict_types=1);

namespace Src\Domain\Drivers\Services;

use Src\Domain\Drivers\Contracts\DriverClaimer;
use Src\Domain\Drivers\Enums\DriverStatus;
use Src\Domain\Drivers\Models\Entities\Driver;

final class EloquentDriverClaimer implements DriverClaimer
{
    public function claim(int $driverId, int $orderId): bool
    {
        // lockForUpdate blocks until any competing assignment of this driver
        // commits; assignable() then tells us whether they're still free.
        $driver = Driver::query()
            ->whereKey($driverId)
            ->assignable()
            ->lockForUpdate()
            ->first();

        if ($driver === null) {
            return false; // taken by a competing request — race lost
        }

        $driver->status = DriverStatus::Busy;
        $driver->current_order_id = $orderId;
        $driver->save();

        return true;
    }
}
