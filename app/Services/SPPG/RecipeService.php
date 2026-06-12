<?php

namespace App\Services\SPPG;

use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Ingredient;
use Illuminate\Support\Facades\DB;

class RecipeService
{
    public function getAll(int $sppgId, array $filters = [])
    {
        $query = Recipe::with(['recipeIngredients.ingredient'])->where('sppg_id', $sppgId);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $perPage = $filters['per_page'] ?? 15;
        return $query->latest()->paginate($perPage);
    }

    public function getAllForDropdown(int $sppgId)
    {
        return Recipe::where('sppg_id', $sppgId)
            ->select('id', 'sppg_id', 'name', 'total_calorie', 'total_protein', 'total_carbohydrate', 'total_fat', 'total_weight')
            ->orderBy('name')
            ->get();
    }

    public function findById(int $sppgId, int $id): Recipe
    {
        return Recipe::with(['recipeIngredients.ingredient'])
            ->where('sppg_id', $sppgId)
            ->findOrFail($id);
    }

    public function create(int $sppgId, array $data): Recipe
    {
        return DB::transaction(function () use ($sppgId, $data) {
            $nutritionTotals = $this->calculateTotalNutrition($sppgId, $data['ingredients']);

            $recipe = Recipe::create([
                'sppg_id'             => $sppgId,
                'name'                => $data['name'],
                'description'         => $data['description'] ?? null,
                'target_calorie'      => $data['target_calorie'] ?? 0,
                'target_protein'      => $data['target_protein'] ?? 0,
                'target_carbohydrate' => $data['target_carbohydrate'] ?? 0,
                'target_fat'          => $data['target_fat'] ?? 0,
                'total_calorie'       => $nutritionTotals['total_calorie'],
                'total_protein'       => $nutritionTotals['total_protein'],
                'total_carbohydrate'  => $nutritionTotals['total_carbohydrate'],
                'total_fat'           => $nutritionTotals['total_fat'],
                'total_weight'        => $nutritionTotals['total_weight'],
            ]);

            $this->saveRecipeIngredients($sppgId, $recipe, $data['ingredients']);

            return $recipe->load('recipeIngredients.ingredient');
        });
    }

    public function update(int $sppgId, int $id, array $data): Recipe
    {
        return DB::transaction(function () use ($sppgId, $id, $data) {
            $recipe = Recipe::where('sppg_id', $sppgId)->findOrFail($id);
            $recipe->recipeIngredients()->delete();

            $nutritionTotals = $this->calculateTotalNutrition($sppgId, $data['ingredients']);

            $recipe->update([
                'name'                => $data['name'],
                'description'         => $data['description'] ?? $recipe->description,
                'target_calorie'      => $data['target_calorie'] ?? $recipe->target_calorie,
                'target_protein'      => $data['target_protein'] ?? $recipe->target_protein,
                'target_carbohydrate' => $data['target_carbohydrate'] ?? $recipe->target_carbohydrate,
                'target_fat'          => $data['target_fat'] ?? $recipe->target_fat,
                'total_calorie'       => $nutritionTotals['total_calorie'],
                'total_protein'       => $nutritionTotals['total_protein'],
                'total_carbohydrate'  => $nutritionTotals['total_carbohydrate'],
                'total_fat'           => $nutritionTotals['total_fat'],
                'total_weight'        => $nutritionTotals['total_weight'],
            ]);

            $this->saveRecipeIngredients($sppgId, $recipe, $data['ingredients']);

            return $recipe->load('recipeIngredients.ingredient');
        });
    }

    public function delete(int $sppgId, int $id): bool
    {
        $recipe = Recipe::where('sppg_id', $sppgId)->findOrFail($id);
        $usedInMenus = $recipe->menuItems()->count();

        if ($usedInMenus > 0) {
            throw new \Exception(
                "Resep '{$recipe->name}' masih digunakan di {$usedInMenus} menu dan tidak dapat dihapus.",
                409
            );
        }

        return $recipe->delete();
    }

    private function calculateTotalNutrition(int $sppgId, array $ingredientsList): array
    {
        $totals = [
            'total_calorie'      => 0,
            'total_protein'      => 0,
            'total_carbohydrate' => 0,
            'total_fat'          => 0,
            'total_weight'       => 0,
        ];

        foreach ($ingredientsList as $item) {
            $ingredient = Ingredient::where('sppg_id', $sppgId)->findOrFail($item['ingredient_id']);
            $nutrition  = $ingredient->calculateNutritionFor($item['weight_used']);

            $totals['total_calorie']      += $nutrition['calorie'];
            $totals['total_protein']      += $nutrition['protein'];
            $totals['total_carbohydrate'] += $nutrition['carbohydrate'];
            $totals['total_fat']          += $nutrition['fat'];
            $totals['total_weight']       += $item['weight_used'];
        }

        return array_map(fn($v) => round($v, 2), $totals);
    }

    private function saveRecipeIngredients(int $sppgId, Recipe $recipe, array $ingredientsList): void
    {
        foreach ($ingredientsList as $index => $item) {
            $ingredient = Ingredient::where('sppg_id', $sppgId)->findOrFail($item['ingredient_id']);
            $nutrition  = $ingredient->calculateNutritionFor($item['weight_used']);

            RecipeIngredient::create([
                'recipe_id'                => $recipe->id,
                'ingredient_id'            => $item['ingredient_id'],
                'weight_used'              => $item['weight_used'],
                'calorie_contribution'     => $nutrition['calorie'],
                'protein_contribution'     => $nutrition['protein'],
                'carbohydrate_contribution'=> $nutrition['carbohydrate'],
                'fat_contribution'         => $nutrition['fat'],
                'order'                    => $index + 1,
            ]);
        }
    }
}