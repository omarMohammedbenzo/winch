<?php

declare(strict_types=1);

namespace Src\Presentation\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Src\Domain\Drivers\Actions\SearchDrivers;
use Src\Presentation\Admin\Requests\SearchDriversRequest;
use Src\Presentation\Admin\Resources\DriverResource;

/**
 * GET /api/drivers — search drivers by name or phone (paginated).
 */
final class DriverController extends Controller
{
    public function __invoke(SearchDriversRequest $request, SearchDrivers $action): AnonymousResourceCollection
    {
        return DriverResource::collection(
            $action->execute($request->searchTerm(), $request->perPage()),
        );
    }
}
