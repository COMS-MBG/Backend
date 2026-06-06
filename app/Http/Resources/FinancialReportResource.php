<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a single financial delivery cost row.
 *
 * The resource wraps a plain array (not an Eloquent model).
 */
class FinancialReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'no'            => $data['no']            ?? null,
            'date'          => $data['date'],
            'school_name'   => $data['school_name'],
            'courier_name'  => $data['courier_name'],
            'vehicle_type'  => $data['vehicle_type'],
            'vehicle_plate' => $data['vehicle_plate'] ?? null,
            'distance_km'   => $data['distance_km'],
            'rate_per_km'   => $data['rate_per_km'],
            'total_cost'    => $data['total_cost'],
            'departed_at'   => isset($data['departed_at'])
                ? \Carbon\Carbon::parse($data['departed_at'])->toIso8601String()
                : null,
            'arrived_at'    => isset($data['arrived_at'])
                ? \Carbon\Carbon::parse($data['arrived_at'])->toIso8601String()
                : null,
        ];
    }
}
