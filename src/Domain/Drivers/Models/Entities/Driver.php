<?php

declare(strict_types=1);

namespace Src\Domain\Drivers\Models\Entities;

use Database\Factories\DriverFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\Drivers\Enums\DriverStatus;

/**
 * @property int $id
 * @property string $name
 * @property string $phone
 * @property DriverStatus $status
 * @property float $latitude
 * @property float $longitude
 * @property int|null $current_order_id
 */
class Driver extends Model
{
    use HasFactory;

    protected $table = 'drivers';

    /** Models live outside app/Models, so point the factory resolver explicitly. */
    protected static function newFactory(): Factory
    {
        return DriverFactory::new();
    }

    protected $fillable = [
        'name',
        'phone',
        'status',
        'latitude',
        'longitude',
        'current_order_id',
    ];

    protected $casts = [
        'status' => DriverStatus::class,
        'latitude' => 'float',
        'longitude' => 'float',
        'current_order_id' => 'integer',
    ];

    /**
     * Explicit local scope (not a global scope) so it never hides rows implicitly.
     */
    public function scopeAssignable(Builder $query): Builder
    {
        return $query
            ->where('status', DriverStatus::Available)
            ->whereNull('current_order_id');
    }

    /**
     * Free-text search over name or phone. No-op when the term is empty.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    /**
     * Bounding-box pre-filter around a point (degrees), to cut candidates
     * before the exact ST_Distance_Sphere() ranking. Uses the lat/lng indexes.
     */
    public function scopeWithinBoundingBox(Builder $query, float $latitude, float $longitude, float $radiusKm): Builder
    {
        // ~111km per degree of latitude; longitude shrinks by cos(lat).
        $latDelta = $radiusKm / 111.0;
        $lngDelta = $radiusKm / (111.0 * max(cos(deg2rad($latitude)), 0.01));

        return $query
            ->whereBetween('latitude', [$latitude - $latDelta, $latitude + $latDelta])
            ->whereBetween('longitude', [$longitude - $lngDelta, $longitude + $lngDelta]);
    }

    
    /**
     *  This is a conversion from kilometers to degrees on the horizontal axis.
     *  The Earth is spherical, so the degree of longitude isn't constant—it decreases as we get closer to the poles,
     *  and is multiplied by the cosine of latitude. At the equator, one degree of longitude equals 111 km. In Cairo (30°),
     *  it becomes 96 km. At the pole, it becomes zero. If you don't account for this,
     *  the bounding box in the north will be wider than required and narrower in the south,
     *  potentially missing nearby drivers. The max(cos(...), 0.01) is a safety net to prevent dividing by zero if someone turns near the poles. 
     *  The overall concept: the bounding box rectangle uses the indexes on latitude and longitude, narrowing down the candidates from millions to tens, 
     *  and then ST_Distance_Sphere calculates the precise distance for a small number. This is the standard pattern for geospatial queries.
     *  */ 
}
