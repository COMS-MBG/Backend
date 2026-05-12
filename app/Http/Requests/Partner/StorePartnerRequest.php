<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;

class StorePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by route middleware
    }

    public function rules(): array
    {
        return [
            'nama_sekolah'   => 'required|string|max:255',
            'npsn'           => 'nullable|string|max:50|unique:partners,npsn',
            'bentuk'         => 'required|string|in:SD,SMP,SMA,SMK,MI,MTs,MA,MAK',
            'status'         => 'required|string|in:Negeri,Swasta',
            'alamat'         => 'nullable|string',
            'kecamatan'      => 'nullable|string|max:100',
            'kabupaten_kota' => 'nullable|string|max:100',
            'latitude'       => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
            'jumlah_porsi'   => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_sekolah.required' => 'Nama sekolah wajib diisi.',
            'bentuk.required'       => 'Bentuk sekolah wajib dipilih.',
            'bentuk.in'             => 'Bentuk sekolah tidak valid.',
            'status.required'       => 'Status sekolah wajib dipilih.',
            'status.in'             => 'Status harus Negeri atau Swasta.',
            'npsn.unique'           => 'NPSN sudah terdaftar.',
            'jumlah_porsi.required' => 'Jumlah porsi wajib diisi.',
            'jumlah_porsi.min'      => 'Jumlah porsi tidak boleh negatif.',
        ];
    }
}
