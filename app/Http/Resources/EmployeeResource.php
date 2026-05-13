<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'nik'         => $this->nik,
            'position'    => $this->position,
            'phone'       => $this->phone,
            'address'     => $this->address,
            'joined_at'   => $this->joined_at?->toDateString(),
            'status'      => $this->status,
            'photo'       => $this->photo ? asset('storage/' . $this->photo) : null,
            'sppg_id'     => $this->sppg_id,
            'has_account' => $this->user_id !== null,
            'user'        => $this->whenLoaded('user', fn() => [
                'id'    => $this->user?->id,
                'name'  => $this->user?->name,
                'email' => $this->user?->email,
            ]),
            'role'        => $this->whenLoaded('role', fn() => [
                'id'   => $this->role?->id,
                'name' => $this->role?->name,
                'slug' => $this->role?->slug,
            ]),
            // Gaji hanya terlihat oleh pemilik/manajer/super_admin
            'base_salary' => $this->when(
                $request->user()?->hasAnyRole(['super_admin', 'pemilik', 'manajer']),
                $this->base_salary
            ),
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}