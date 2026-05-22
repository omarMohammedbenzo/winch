<?php

declare(strict_types=1);

namespace Src\Presentation\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Src\Domain\Orders\Actions\ListOrders;
use Src\Presentation\Admin\Requests\ListOrdersRequest;
use Src\Presentation\Admin\Resources\OrderResource;

/**
 * GET /api/orders — orders for the dispatcher screen, filterable
 * (active | all | a specific status) and paginated.
 */
final class OrderController extends Controller
{
    public function __invoke(ListOrdersRequest $request, ListOrders $action): AnonymousResourceCollection
    {
        return OrderResource::collection(
            $action->execute($request->filterValue(), $request->perPage()),
        );
    }
}
