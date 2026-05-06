<?php

namespace App\Http\Requests\Nutrition;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * REQUEST = Validasi input sebelum masuk ke Controller.
 * Analogi NestJS: DTO + class-validator (@IsString, @IsNumber, dll)
 *
 * File ini dipakai untuk POST (create) dan PUT (update) ingredient.
 */
class IngredientRequest extends FormRequest
{
    /**
     * Siapa yang boleh mengakses endpoint ini?
     * Kembalikan true = semua boleh (atur di middleware route jika perlu auth)
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi
     * Analogi NestJS: class-validator decorators di DTO
     */
    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'carbohydrate'   => 'required|numeric|min:0',
            'protein'        => 'required|numeric|min:0',
            'calorie'        => 'required|numeric|min:0',
            'fat'            => 'nullable|numeric|min:0',
            'serving_weight' => 'required|numeric|min:1',
            'description'    => 'nullable|string|max:1000',
        ];
    }

    /**
     * Pesan error kustom
     */
    public function messages(): array
    {
        return [
            'name.required'           => 'Nama bahan baku wajib diisi.',
            'carbohydrate.required'   => 'Nilai karbohidrat wajib diisi.',
            'carbohydrate.numeric'    => 'Nilai karbohidrat harus berupa angka.',
            'protein.required'        => 'Nilai protein wajib diisi.',
            'protein.numeric'         => 'Nilai protein harus berupa angka.',
            'calorie.required'        => 'Nilai kalori wajib diisi.',
            'calorie.numeric'         => 'Nilai kalori harus berupa angka.',
            'fat.numeric'             => 'Nilai lemak harus berupa angka.',
            'serving_weight.required' => 'Berat acuan wajib diisi.',
            'serving_weight.min'      => 'Berat acuan minimal 1 gram.',
        ];
    }

    /**
     * Override response error agar selalu return JSON (bukan redirect HTML).
     * PENTING untuk API backend — tanpa ini Laravel bisa return redirect 302.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}