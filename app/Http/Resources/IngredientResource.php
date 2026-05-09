<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * RESOURCE = Format output JSON yang dikirim ke FE.
 * Analogi NestJS: Serializer / Interceptor / DTO Response
 *
 * Keuntungan pakai Resource:
 * - Bisa sembunyikan kolom sensitif (deleted_at, password, dll)
 * - Bisa tambah field kalkulasi
 * - Konsisten di semua endpoint
 */
class IngredientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'carbohydrate'   => $this->carbohydrate,
            'protein'        => $this->protein,
            'calorie'        => $this->calorie,
            'fat'            => $this->fat ?? 0,
            'serving_weight' => $this->serving_weight,
            'description'    => $this->description,

            // Info kalkulasi per gram (berguna untuk FE live calculation)
            'nutrition_per_gram' => [
                'calorie'      => round($this->calorie / $this->serving_weight, 4),
                'protein'      => round($this->protein / $this->serving_weight, 4),
                'carbohydrate' => round($this->carbohydrate / $this->serving_weight, 4),
                'fat'          => round(($this->fat ?? 0) / $this->serving_weight, 4),
            ],

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}