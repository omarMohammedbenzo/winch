<?php

declare(strict_types=1);

namespace Src\Domain\Drivers\Enums;

enum DriverStatus: string
{
    case Available = 'available';
    case Busy = 'busy';
    case Offline = 'offline';

    /**
     * A driver can receive an order only when available.
     */
    public function canAcceptOrder(): bool
    {
        return $this === self::Available;
    }
}
