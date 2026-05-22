<?php

declare(strict_types=1);

namespace Src\Domain\Drivers\Contracts;

/**
 * The Drivers domain's gateway for atomically claiming a driver.
 *
 * Separating "claim" (write) from "find" (look DriverFinder) keeps all
 * mutation of the Driver model inside the Drivers domain, so the Orders domain
 * never touches a Driver row directly.
 */
interface DriverClaimer
{
    public function claim(int $driverId, int $orderId): bool;
}
