<?php

declare(strict_types=1);

namespace Src\Presentation\Admin\Controllers;

use App\Http\Controllers\Controller;
use Src\Domain\Orders\Contracts\OrderAssigner;
use Src\Presentation\Admin\Resources\AssignmentResource;

/**
 * POST /api/orders/{order}/assign
 *
 * Thin: delegates to the Orders domain via the OrderAssigner contract. All
 * rules, locking and failure modes live in the domain; failures surface as
 * domain exceptions mapped to HTTP status codes centrally.
 */
final class OrderAssignmentController extends Controller
{
    public function __construct(private readonly OrderAssigner $assigner) {}

    public function __invoke(int $order): AssignmentResource
    {
        return new AssignmentResource($this->assigner->assign($order));
    }
}
