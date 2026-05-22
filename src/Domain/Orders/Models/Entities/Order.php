<?php

declare(strict_types=1);

namespace Src\Domain\Orders\Models\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\Orders\Enums\OrderStatus;

/**
 * @property int $id
 * @property string $reference
 * @property OrderStatus $status
 * @property float $pickup_latitude
 * @property float $pickup_longitude
 * @property float|null $dropoff_latitude
 * @property float|null $dropoff_longitude
 * @property int|null $driver_id
 * @property \Illuminate\Support\Carbon|null $assigned_at
 */
class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'reference',
        'status',
        'pickup_latitude',
        'pickup_longitude',
        'dropoff_latitude',
        'dropoff_longitude',
        'driver_id',
        'assigned_at',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'pickup_latitude' => 'float',
        'pickup_longitude' => 'float',
        'dropoff_latitude' => 'float',
        'dropoff_longitude' => 'float',
        'assigned_at' => 'datetime',
    ];

    /**
     * Auto-generate the UUID reference on create.
     */
    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            $order->reference ??= (string) \Illuminate\Support\Str::uuid();
        });
    }

    /** Orders visible on the dispatcher's active-orders screen. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', OrderStatus::active());
    }

    public function scopeForDriver(Builder $query, int $driverId): Builder
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeWithStatus(Builder $query, OrderStatus $status): Builder
    {
        return $query->where('status', $status);
    }
}
