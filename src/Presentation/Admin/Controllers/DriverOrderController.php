<?php

declare(strict_types=1);

namespace Src\Presentation\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Src\Domain\Drivers\Models\Entities\Driver;
use Src\Domain\Orders\Actions\ListDriverOrders;
use Src\Presentation\Admin\Requests\ListDriverOrdersRequest;
use Src\Presentation\Admin\Resources\OrderResource;

/**
 * GET /api/drivers/{driver}/orders
 */
final class DriverOrderController extends Controller
{
    public function __invoke(
        Driver $driver,
        ListDriverOrdersRequest $request,
        ListDriverOrders $action,
    ): AnonymousResourceCollection {
        $orders = $action->execute(
            driverId: $driver->id,
            status: $request->statusFilter(),
            perPage: $request->perPage(),
        );

        return OrderResource::collection($orders);
    }
}
