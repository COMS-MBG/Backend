<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'nama_sekolah'   => $this->nama_sekolah,
            'npsn'           => $this->npsn,
            'bentuk'         => $this->bentuk,
            'status'         => $this->status,
            'alamat'         => $this->alamat,
            'kecamatan'      => $this->kecamatan,
            'kabupaten_kota' => $this->kabupaten_kota,
            'latitude'       => $this->latitude,
            'longitude'      => $this->longitude,
            'jumlah_porsi'   => $this->jumlah_porsi,
            'sppg_id'        => $this->sppg_id,
            'sppg'           => $this->whenLoaded('sppg', fn() => [
                'id'   => $this->sppg?->id,
                'nama' => $this->sppg?->nama,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
