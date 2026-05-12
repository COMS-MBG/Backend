<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by route middleware
    }

    public function rules(): array
    {
        $partnerId = $this->route('partner') ?? $this->route('id');

        return [
            'nama_sekolah'   => 'sometimes|required|string|max:255',
            'npsn'           => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('partners', 'npsn')->ignore($partnerId),
            ],
            'bentuk'         => 'sometimes|required|string|in:SD,SMP,SMA,SMK,MI,MTs,MA,MAK',
            'status'         => 'sometimes|required|string|in:Negeri,Swasta',
            'alamat'         => 'nullable|string',
            'kecamatan'      => 'nullable|string|max:100',
            'kabupaten_kota' => 'nullable|string|max:100',
            'latitude'       => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
            'jumlah_porsi'   => 'sometimes|required|integer|min:0',
        ];
    }
}
