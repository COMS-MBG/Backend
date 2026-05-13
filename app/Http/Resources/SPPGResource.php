<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SPPGResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'address'     => $this->address,
            'coordinates' => [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ],
            'capacity'    => $this->capacity,
            'status'      => $this->status,
            'phone'       => $this->phone,
            'email'       => $this->email,
            'region'      => [
                'district' => $this->district,
                'city'     => $this->city,
                'province' => $this->province,
            ],
            'owner'       => $this->whenLoaded('owner', fn() => [
                'id'   => $this->owner?->id,
                'name' => $this->owner?->name,
            ]),
            'schools_count'    => $this->whenCounted('schools'),
            'schools'          => SchoolResource::collection($this->whenLoaded('schools')),
            'capacity_status'  => $this->when(
                isset($this->schools_count),
                fn() => [
                    'filled'     => $this->schools_count ?? 0,
                    'percentage' => $this->capacity > 0
                        ? round((($this->schools_count ?? 0) / $this->capacity) * 100, 1)
                        : 0,
                    'full'       => ($this->schools_count ?? 0) >= $this->capacity,
                ]
            ),
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}