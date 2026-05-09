<?php

namespace App\Http\Requests\SPPG;

use Illuminate\Foundation\Http\FormRequest;

class StoreSPPGRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sppg.create');
    }

    public function rules(): array
    {
        return [
            'nama'       => 'required|string|max:255',
            'alamat'     => 'required|string',
            'latitude'   => 'required|numeric|between:-90,90',
            'longitude'  => 'required|numeric|between:-180,180',
            'kapasitas'  => 'required|integer|min:1|max:100',
            'status'     => 'sometimes|in:aktif,nonaktif,pengajuan',
            'telepon'    => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:255',
            'kecamatan'  => 'nullable|string|max:100',
            'kota'       => 'nullable|string|max:100',
            'provinsi'   => 'nullable|string|max:100',
            'pemilik_id' => 'nullable|uuid|exists:users,id',
            'school_ids' => 'nullable|array',
            'school_ids.*' => 'uuid|exists:schools,id',
        ];
    }
}