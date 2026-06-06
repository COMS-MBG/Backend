<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    protected $table = 'shipping_rates';

    protected $fillable = [
        'vehicle_type',
        'rate_per_km',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'rate_per_km' => 'float',
        'is_active'   => 'boolean',
    ];

    // Vehicle type constants — matches DeliverySchedule constants
    const VEHICLE_MOTORCYCLE = 'motorcycle';
    const VEHICLE_CAR        = 'car';
    const VEHICLE_VAN        = 'van';
    const VEHICLE_TRUCK      = 'truck';

    /**
     * Get the active rate for a given vehicle type.
     * Returns 0 if no rate is configured.
     */
    public static function getRateFor(string $vehicleType): float
    {
        return (float) static::where('vehicle_type', $vehicleType)
            ->where('is_active', true)
            ->value('rate_per_km') ?? 0.0;
    }

    /**
     * Scope: only active rates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
