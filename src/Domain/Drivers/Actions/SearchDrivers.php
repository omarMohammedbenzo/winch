<?php

declare(strict_types=1);

namespace Src\Domain\Drivers\Actions;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Domain\Drivers\Enums\DriverStatus;
use Src\Domain\Drivers\Models\Entities\Driver;

final class SearchDrivers
{
    public function execute(?string $term, ?DriverStatus $status = null, int $perPage = 10): LengthAwarePaginator
    {
        return Driver::query()
            ->search($term)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate($perPage);
    }
}
