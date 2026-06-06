<?php

namespace App\Http\Resources\Distribution;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DeliveryScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'status'        => $this->status,
            'vehicle_type'  => $this->vehicle_type,
            'vehicle_plate' => $this->vehicle_plate,
            'scheduled_at'  => $this->scheduled_at?->toIso8601String(),
            'departed_at'   => $this->departed_at?->toIso8601String(),
            'arrived_at'    => $this->arrived_at?->toIso8601String(),

            // Courier
            'courier' => $this->whenLoaded('courier', fn() => [
                'id'   => $this->courier->id,
                'name' => $this->courier->name,
            ]),

            // School / destination
            'school' => $this->whenLoaded('school', fn() => [
                'id'        => $this->school->id,
                'name'      => $this->school->name     ?? null,
                'address'   => $this->school->address  ?? null,
                'latitude'  => $this->school->latitude  ?? null,
                'longitude' => $this->school->longitude ?? null,
            ]),

            // Assigned / submitted by
            'assigned_by'  => $this->whenLoaded('assignedBy',  fn() => $this->assignedBy?->name),
            'submitted_by' => $this->whenLoaded('submittedBy', fn() => $this->submittedBy?->name),

            // Notes
            'delivery_notes' => $this->delivery_notes,

            // Rejection
            'rejection' => $this->when(
                $this->status === 'rejected',
                fn() => [
                    'reason'      => $this->rejection_reason,
                    'photo_url'   => $this->rejection_photo_path
                        ? Storage::url($this->rejection_photo_path)
                        : null,
                    'rejected_at' => $this->rejected_at?->toIso8601String(),
                ]
            ),

            // Delivery proof
            'proof' => $this->when(
                in_array($this->status, ['delivered', 'confirmed', 'revision_required']),
                fn() => [
                    'photo_url'    => $this->proof_photo_path
                        ? Storage::url($this->proof_photo_path)
                        : null,
                    'submitted_at' => $this->proof_submitted_at?->toIso8601String(),
                ]
            ),

            // Confirmation
            'confirmation' => $this->when(
                $this->status === 'confirmed',
                fn() => [
                    'confirmed_by' => $this->whenLoaded('confirmedBy', fn() => $this->confirmedBy?->name),
                    'confirmed_at' => $this->confirmed_at?->toIso8601String(),
                    'notes'        => $this->confirmation_notes,
                ]
            ),

            // Revision request notes
            'revision_notes' => $this->when(
                $this->status === 'revision_required',
                $this->confirmation_notes
            ),

            // Live location (only when delivering)
            'latest_location' => $this->when(
                $this->status === 'delivering' && $this->relationLoaded('latestLocation'),
                fn() => $this->latestLocation ? [
                    'latitude'    => $this->latestLocation->latitude,
                    'longitude'   => $this->latestLocation->longitude,
                    'recorded_at' => $this->latestLocation->recorded_at->toIso8601String(),
                ] : null
            ),

            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
