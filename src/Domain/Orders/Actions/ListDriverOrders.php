<?php

declare(strict_types=1);

namespace Src\Domain\Orders\Actions;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Domain\Orders\Enums\OrderStatus;
use Src\Domain\Orders\Models\Entities\Order;


final class ListDriverOrders
{
    public function execute(int $driverId, ?OrderStatus $status, int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->forDriver($driverId)
            ->when($status, fn ($query) => $query->withStatus($status))
            ->latest()
            ->paginate($perPage);
    }
}
