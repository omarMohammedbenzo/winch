<?php

namespace Src\Presentation\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Src\Domain\Orders\Models\Entities\Order;

/**
 * @mixin Order
 */
final class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status->value,
            'status_label' => __('orders.statuses.'.$this->status->value),
            'pickup' => [
                'lat' => $this->pickup_latitude,
                'lng' => $this->pickup_longitude,
            ],
            'driver_id' => $this->driver_id,
            'assigned_at' => $this->assigned_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
