<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'nama'            => $this->nama,
            'alamat'          => $this->alamat,
            'koordinat'       => [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ],
            'jumlah_siswa'    => $this->jumlah_siswa,
            'jenjang'         => $this->jenjang,
            'kecamatan'       => $this->kecamatan,
            'kota'            => $this->kota,
            'provinsi'        => $this->provinsi,
            'telepon'         => $this->telepon,
            'kepala_sekolah'  => $this->kepala_sekolah,
            'status'          => $this->status,
            'sppg_id'         => $this->sppg_id,
            'sppg'            => $this->whenLoaded('sppg', fn() => [
                'id'   => $this->sppg?->id,
                'nama' => $this->sppg?->nama,
            ]),
            'jarak_ke_sppg_km' => $this->when(
                $this->relationLoaded('sppg') && $this->sppg,
                fn() => $this->distanceToSppg()
            ),
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}