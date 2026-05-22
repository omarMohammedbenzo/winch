<?php

declare(strict_types=1);

namespace Src\Domain\Orders\Exceptions;

/**
 * Raised when the order to act on does not exist. Maps to 404 Not Found.
 *
 * A domain exception (not Eloquent's ModelNotFoundException) so it carries a
 * stable code + localized message and isn't silently rewritten to a generic
 * NotFoundHttpException by the framework before our handler sees it.
 */
final class OrderNotFound extends OrderException
{
    public function __construct(public readonly int $orderId)
    {
        parent::__construct("Order #{$orderId} was not found.");
    }

    public function errorCode(): string
    {
        return 'order_not_found';
    }

    public function translationKey(): string
    {
        return 'orders.not_found';
    }

    protected function replacements(?string $locale): array
    {
        return ['id' => $this->orderId];
    }
}
