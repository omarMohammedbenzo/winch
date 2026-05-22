<?php

namespace Src\Presentation\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Src\Domain\Orders\DataTransferObjects\OrderAssignmentResult;

/**
 * Shapes the result of POST /orders/{id}/assign.
 *
 * @property OrderAssignmentResult $resource
 */
final class AssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->resource->orderId,
            'reference' => $this->resource->reference,
            'driver_id' => $this->resource->driverId,
            'distance_meters' => round($this->resource->distanceMeters, 1),
            'assigned_at' => $this->resource->assignedAt,
        ];
    }
}
