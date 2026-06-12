<?php

namespace App\Services\SPPG;

use App\Models\Ingredient;
use Illuminate\Pagination\LengthAwarePaginator;

class IngredientService
{
    public function getAll(int $sppgId, array $filters = []): LengthAwarePaginator
    {
        $query = Ingredient::query()->where('sppg_id', $sppgId);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $perPage = $filters['per_page'] ?? 15;
        return $query->latest()->paginate($perPage);
    }

    public function getAllForDropdown(int $sppgId)
    {
        return Ingredient::where('sppg_id', $sppgId)
            ->select('id', 'sppg_id', 'name', 'calorie', 'protein', 'carbohydrate', 'fat', 'serving_weight')
            ->orderBy('name')
            ->get();
    }

    public function findById(int $sppgId, int $id): Ingredient
    {
        return Ingredient::where('sppg_id', $sppgId)->findOrFail($id);
    }

    public function create(int $sppgId, array $data): Ingredient
    {
        $data['sppg_id'] = $sppgId;
        return Ingredient::create($data);
    }

    public function update(int $sppgId, int $id, array $data): Ingredient
    {
        $ingredient = $this->findById($sppgId, $id);
        unset($data['sppg_id']);
        $ingredient->update($data);
        return $ingredient->fresh();
    }

    public function delete(int $sppgId, int $id): bool
    {
        $ingredient = $this->findById($sppgId, $id);
        $usedInRecipes = $ingredient->recipeIngredients()->count();

        if ($usedInRecipes > 0) {
            throw new \Exception(
                "Ingredient '{$ingredient->name}' masih digunakan di {$usedInRecipes} resep dan tidak dapat dihapus.",
                409
            );
        }

        return $ingredient->delete();
    }

    public function calculateNutritionPreview(int $sppgId, int $ingredientId, float $weight): array
    {
        $ingredient = $this->findById($sppgId, $ingredientId);
        $nutrition  = $ingredient->calculateNutritionFor($weight);

        return [
            'ingredient_id'   => $ingredient->id,
            'ingredient_name' => $ingredient->name,
            'weight_used'     => $weight,
            'nutrition'       => $nutrition,
        ];
    }
}