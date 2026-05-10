<?php

namespace App\Http\Requests\SPPG;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSPPGRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sppg.edit');
    }

    public function rules(): array
    {
        return [
            'nama'       => 'sometimes|string|max:255',
            'alamat'     => 'sometimes|string',
            'latitude'   => 'sometimes|numeric|between:-90,90',
            'longitude'  => 'sometimes|numeric|between:-180,180',
            'kapasitas'  => 'sometimes|integer|min:1|max:100',
            'status'     => 'sometimes|in:aktif,nonaktif,pengajuan',
            'telepon'    => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:255',
            'kecamatan'  => 'nullable|string|max:100',
            'kota'       => 'nullable|string|max:100',
            'provinsi'   => 'nullable|string|max:100',
            'pemilik_id' => 'nullable|uuid|exists:users,id',
        ];
    }
}