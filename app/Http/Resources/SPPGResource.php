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
            'nama'        => $this->nama,
            'alamat'      => $this->alamat,
            'koordinat'   => [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ],
            'kapasitas'   => $this->kapasitas,
            'status'      => $this->status,
            'telepon'     => $this->telepon,
            'email'       => $this->email,
            'wilayah'     => [
                'kecamatan' => $this->kecamatan,
                'kota'      => $this->kota,
                'provinsi'  => $this->provinsi,
            ],
            'pemilik'     => $this->whenLoaded('pemilik', fn() => [
                'id'   => $this->pemilik?->id,
                'nama' => $this->pemilik?->nama,
            ]),
            'sekolah_count'   => $this->whenCounted('schools'),
            'sekolah'         => SchoolResource::collection($this->whenLoaded('schools')),
            'kapasitas_status' => $this->when(
                isset($this->schools_count),
                fn() => [
                    'terisi'     => $this->schools_count ?? 0,
                    'persentase' => $this->kapasitas > 0
                        ? round((($this->schools_count ?? 0) / $this->kapasitas) * 100, 1)
                        : 0,
                    'penuh'      => ($this->schools_count ?? 0) >= $this->kapasitas,
                ]
            ),
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}