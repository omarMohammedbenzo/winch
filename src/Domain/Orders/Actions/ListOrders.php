<?php

declare(strict_types=1);

namespace Src\Domain\Orders\Actions;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Domain\Orders\Enums\OrderStatus;
use Src\Domain\Orders\Models\Entities\Order;

/**
 * Read use-case for the dispatcher's orders screen, newest first.
 *
 * Filter semantics:
 *   - "active" (default) → pending / assigned / in_progress
 *   - "all"              → no status constraint
 *   - a specific status  → exactly that status (incl. completed / cancelled)
 */
final class ListOrders
{
    public function execute(string $filter = 'active', int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::query();

        if ($filter === 'active') {
            $query->active();
        } elseif ($filter !== 'all') {
            $query->withStatus(OrderStatus::from($filter));
        }

        return $query->latest()->paginate($perPage);
    }
}
