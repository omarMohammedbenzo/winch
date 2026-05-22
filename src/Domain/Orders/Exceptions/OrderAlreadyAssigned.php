<?php

declare(strict_types=1);

namespace Src\Domain\Orders\Exceptions;

use Src\Domain\Orders\Enums\OrderStatus;

/**
 * Raised when an order can't be assigned because it is no longer pending
 * (already assigned, in progress, completed or cancelled). Maps to 409 Conflict.
 */
final class OrderAlreadyAssigned extends OrderException
{
    public function __construct(
        public readonly int $orderId,
        public readonly OrderStatus $currentStatus,
    ) {
        parent::__construct("Order #{$orderId} is no longer assignable (status: {$currentStatus->value}).");
    }

    public function errorCode(): string
    {
        return 'order_already_assigned';
    }

    public function translationKey(): string
    {
        return 'orders.already_assigned';
    }

    protected function replacements(?string $locale): array
    {
        return [
            'id' => $this->orderId,
            'status' => (string) __('orders.statuses.'.$this->currentStatus->value, [], $locale),
        ];
    }
}
