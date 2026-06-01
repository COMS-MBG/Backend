<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'school_name'      => $this->school_name,
            'npsn'             => $this->npsn,
            'school_type'      => $this->school_type,
            'ownership_status' => $this->ownership_status,
            'address'          => $this->address,
            'district'         => $this->district,
            'city'             => $this->city,
            'latitude'         => $this->latitude,
            'longitude'        => $this->longitude,
            'portion_count'    => $this->portion_count,
            'sppg_id'          => $this->sppg_id,
            'sppg'             => $this->whenLoaded('sppg', fn() => [
                'id'   => $this->sppg?->id,
                'name' => $this->sppg?->name,
            ]),
            'is_public'        => $this->is_public,
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
        ];
    }
}
