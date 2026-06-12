<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DeliveryHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'schedule_id'      => $this->delivery_schedule_id,
            'courier_name'     => $this->courier_name,
            'school_name'      => $this->school_name,
            'school_address'   => $this->school_address,
            'vehicle_type'     => $this->vehicle_type,
            'vehicle_plate'    => $this->vehicle_plate,
            'departed_at'      => $this->departed_at?->toIso8601String(),
            'arrived_at'       => $this->arrived_at?->toIso8601String(),
            'duration_minutes' => $this->duration_minutes,
            'distance_km'      => $this->distance_km,
            'proof_photo_url'  => $this->proof_photo_path
                ? Storage::url($this->proof_photo_path)  // ← ganti ini
                : null,
            'route_snapshot'   => $this->route_snapshot,
            'confirmed_by'     => $this->whenLoaded('confirmedBy', fn() => $this->confirmedBy?->name),
            'confirmed_at'     => $this->confirmed_at?->toIso8601String(),
            'notes'            => $this->notes,
            'created_at'       => $this->created_at->toIso8601String(),
        ];
    }
}