<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'address'       => $this->address,
            'coordinates'   => [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ],
            'student_count' => $this->student_count,
            'school_level'  => $this->school_level,
            'district'      => $this->district,
            'city'          => $this->city,
            'province'      => $this->province,
            'phone'         => $this->phone,
            'principal'     => $this->principal,
            'status'        => $this->status,
            'sppg_id'       => $this->sppg_id,
            'sppg'          => $this->whenLoaded('sppg', fn() => [
                'id'   => $this->sppg?->id,
                'name' => $this->sppg?->name,
            ]),
            'distance_to_sppg_km' => $this->when(
                $this->relationLoaded('sppg') && $this->sppg,
                fn() => $this->distanceToSppg()
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}