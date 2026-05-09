<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('school.edit');
    }

    public function rules(): array
    {
        return [
            'nama'           => 'sometimes|string|max:255',
            'alamat'         => 'sometimes|string',
            'latitude'       => 'sometimes|numeric|between:-90,90',
            'longitude'      => 'sometimes|numeric|between:-180,180',
            'jumlah_siswa'   => 'sometimes|integer|min:0',
            'jenjang'        => 'sometimes|in:SD,SMP,SMA,SMK,MI,MTs,MA',
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