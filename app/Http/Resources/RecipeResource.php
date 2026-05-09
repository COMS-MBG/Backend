<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\RecipeIngredientResource;

/**
 * RESOURCE untuk output Recipe.
 * Menyertakan daftar bahan (recipeIngredients) dan persentase pencapaian target.
 */
class RecipeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,

            // Target nutrisi yang ingin dicapai
            'targets' => [
                'calorie'      => $this->target_calorie,
                'protein'      => $this->target_protein,
                'carbohydrate' => $this->target_carbohydrate,
                'fat'          => $this->target_fat,
            ],

            // Total nutrisi yang berhasil dicapai
            'totals' => [
                'calorie'      => $this->total_calorie,
                'protein'      => $this->total_protein,
                'carbohydrate' => $this->total_carbohydrate,
                'fat'          => $this->total_fat,
                'weight'       => $this->total_weight,
            ],

            // Persentase pencapaian (misal: 500/3200 kalori = 15.6%)
            'achievement' => [
                'calorie_percentage' => $this->calorie_achievement_percentage,
                // Tambahkan protein/carb/fat percentage jika diperlukan FE
            ],

            // Daftar bahan-bahan dalam resep ini
            // whenLoaded = hanya sertakan jika sudah di-load (eager loading)
            // Ini mencegah N+1 query problem
            'ingredients' => RecipeIngredientResource::collection(
                $this->whenLoaded('recipeIngredients')
            ),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}