<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('school.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama'           => 'required|string|max:255',
            'alamat'         => 'required|string',
            'latitude'       => 'required|numeric|between:-90,90',
            'longitude'      => 'required|numeric|between:-180,180',
            'jumlah_siswa'   => 'required|integer|min:0',
            'jenjang'        => 'required|in:SD,SMP,SMA,SMK,MI,MTs,MA',
            'kecamatan'      => 'nullable|string|max:100',
            'kota'           => 'nullable|string|max:100',
            'provinsi'       => 'nullable|string|max:100',
            'telepon'        => 'nullable|string|max:20',
            'kepala_sekolah' => 'nullable|string|max:255',
            'sppg_id'        => 'nullable|uuid|exists:sppgs,id',
            'status'         => 'sometimes|in:aktif,nonaktif',
        ];
    }
}