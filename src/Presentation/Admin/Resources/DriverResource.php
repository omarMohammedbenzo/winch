<?php

declare(strict_types=1);

namespace Src\Presentation\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Src\Domain\Drivers\Models\Entities\Driver;

/**
 * @mixin Driver
 */
final class DriverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'status' => $this->status->value,
            'status_label' => __('drivers.statuses.'.$this->status->value),
            'location' => [
                'lat' => $this->latitude,
                'lng' => $this->longitude,
            ],
            'current_order_id' => $this->current_order_id,
        ];
    }
}
