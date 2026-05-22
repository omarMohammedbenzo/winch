<?php

declare(strict_types=1);

namespace Src\Domain\Orders\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Statuses shown on the dispatcher's "active orders" screen.
     */
    public static function active(): array
    {
        return [self::Pending, self::Assigned, self::InProgress];
    }

    /**
     * Only a pending order can be assigned to a driver.
     */
    public function canBeAssigned(): bool
    {
        return $this === self::Pending;
    }

    /**
     * Whether this status ties up a driver (driver is not free).
     */
    public function occupiesDriver(): bool
    {
        return in_array($this, [self::Assigned, self::InProgress], true);
    }
}
