<?php

declare(strict_types=1);

namespace Src\Domain\Orders\Actions;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Domain\Orders\Models\Entities\Order;

/**
 * Read use-case behind the dispatcher's "active orders" screen:
 * orders still in play (pending / assigned / in progress), newest first.
 */
final class ListActiveOrders
{
    public function execute(int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->active()
            ->latest()
            ->paginate($perPage);
    }
}
