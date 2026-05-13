<?php

namespace App\Http\Requests\Nutrition;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Validasi input untuk create/update Recipe.
 */
class RecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Header resep
            'name'                    => 'required|string|max:255',
            'description'             => 'nullable|string|max:2000',
            'target_calorie'          => 'nullable|numeric|min:0',
            'target_protein'          => 'nullable|numeric|min:0',
            'target_carbohydrate'     => 'nullable|numeric|min:0',
            'target_fat'              => 'nullable|numeric|min:0',

            // Array bahan-bahan
            // 'ingredients' adalah array, minimal 1 bahan
            'ingredients'             => 'required|array|min:1',
            'ingredients.*.ingredient_id' => 'required|integer|exists:ingredients,id',
            // exists:ingredients,id = validasi bahwa ingredient_id ada di tabel ingredients
            'ingredients.*.weight_used'   => 'required|numeric|min:0.1',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                        => 'Nama resep wajib diisi.',
            'ingredients.required'                 => 'Minimal tambahkan 1 bahan baku.',
            'ingredients.min'                      => 'Minimal tambahkan 1 bahan baku.',
            'ingredients.*.ingredient_id.required' => 'Pilih bahan baku untuk setiap baris.',
            'ingredients.*.ingredient_id.exists'   => 'Bahan baku yang dipilih tidak ditemukan.',
            'ingredients.*.weight_used.required'   => 'Isi berat untuk setiap bahan.',
            'ingredients.*.weight_used.min'        => 'Berat minimal 0.1 gram.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $ingredients = $this->input('ingredients', []);
            if (!is_array($ingredients) || empty($ingredients)) return;

            $totalCalorie = 0;
            foreach ($ingredients as $item) {
                if (empty($item['ingredient_id']) || empty($item['weight_used'])) continue;
                $ingredient = \App\Models\Ingredient::find($item['ingredient_id']);
                if ($ingredient) {
                    $nutrition = $ingredient->calculateNutritionFor($item['weight_used']);
                    $totalCalorie += $nutrition['calorie'];
                }
            }

            if ($totalCalorie < 2000 || $totalCalorie > 2700) {
                $validator->errors()->add('ingredients', 'Total kalori belum memenuhi target. Tambahkan menu atau sesuaikan berat bahan baku. (Total: ' . round($totalCalorie, 2) . ' kkal, Target: 2000-2700 kkal)');
            }
        });
    }
}