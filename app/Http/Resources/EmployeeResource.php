<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'nama'              => $this->nama,
            'nik'               => $this->nik,
            'jabatan'           => $this->jabatan,
            'telepon'           => $this->telepon,
            'alamat'            => $this->alamat,
            'tanggal_bergabung' => $this->tanggal_bergabung?->toDateString(),
            'status'            => $this->status,
            'foto'              => $this->foto ? asset('storage/' . $this->foto) : null,
            'sppg_id'           => $this->sppg_id,
            'user'              => $this->whenLoaded('user', fn() => [
                'id'    => $this->user?->id,
                'nama'  => $this->user?->nama,
                'email' => $this->user?->email,
                'roles' => $this->user?->getRoleNames(),
            ]),
            // Gaji hanya terlihat oleh pemilik/manajer/super_admin
            'gaji_pokok' => $this->when(
                $request->user()?->hasAnyRole(['super_admin', 'pemilik', 'manajer']),
                $this->gaji_pokok
            ),
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}