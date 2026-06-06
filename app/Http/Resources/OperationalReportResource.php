<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a single operational activity row.
 *
 * The resource wraps a plain array (not an Eloquent model),
 * so $this->resource is the raw array.
 */
class OperationalReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'no'            => $data['no']            ?? null,
            'type'          => $data['type'],
            'activity_name' => $data['activity_name'],
            'description'   => $data['description'],
            'started_at'    => isset($data['started_at'])
                ? \Carbon\Carbon::parse($data['started_at'])->toIso8601String()
                : null,
            'completed_at'  => isset($data['completed_at'])
                ? \Carbon\Carbon::parse($data['completed_at'])->toIso8601String()
                : null,
            'status'        => $data['status'],
            'reference_id'  => $data['reference_id'] ?? null,
        ];
    }
}
