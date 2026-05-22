<?php

declare(strict_types=1);

namespace Src\Domain\Drivers\Actions;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Domain\Drivers\Models\Entities\Driver;

final class SearchDrivers
{
    public function execute(?string $term, int $perPage = 10): LengthAwarePaginator
    {
        return Driver::query()
            ->search($term)
            ->orderBy('name')
            ->paginate($perPage);
    }
}
