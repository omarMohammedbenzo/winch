<?php

declare(strict_types=1);

namespace Src\Domain\Orders\Exceptions;

/**
 * Raised when no assignable driver could be claimed for an order — either none
 * are nearby/available, or every nearby candidate was taken by a competing
 * request before we could claim them. Maps to 422 Unprocessable Entity.
 */
final class NoAvailableDriver extends OrderException
{
    public function __construct(public readonly int $orderId)
    {
        parent::__construct("No available driver could be assigned to order #{$orderId}.");
    }

    public function errorCode(): string
    {
        return 'no_available_driver';
    }

    public function translationKey(): string
    {
        return 'orders.no_available_driver';
    }

    protected function replacements(?string $locale): array
    {
        return ['id' => $this->orderId];
    }
}
