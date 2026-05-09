<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * RESOURCE untuk setiap baris bahan dalam resep.
 */
class RecipeIngredientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'order'       => $this->order,
            'weight_used' => $this->weight_used,

            // Detail bahan bakunya
            'ingredient' => $this->whenLoaded('ingredient', fn() => [
                'id'             => $this->ingredient->id,
                'name'           => $this->ingredient->name,
                'serving_weight' => $this->ingredient->serving_weight,
            ]),

            // Kontribusi nutrisi dari bahan ini
            'contribution' => [
                'calorie'      => $this->calorie_contribution,
                'protein'      => $this->protein_contribution,
                'carbohydrate' => $this->carbohydrate_contribution,
                'fat'          => $this->fat_contribution,
            ],
        ];
    }
}