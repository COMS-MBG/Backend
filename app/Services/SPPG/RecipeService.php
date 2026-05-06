<?php

namespace App\Services\SPPG;

use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Ingredient;
use Illuminate\Support\Facades\DB;

/**
 * SERVICE untuk fitur Master Resep.
 * Bagian terpenting: logika kalkulasi gizi otomatis saat resep disimpan.
 *
 * Analogi NestJS: RecipeService (*.service.ts)
 */
class RecipeService
{
    /**
     * Ambil semua resep dengan total nutrisinya
     *
     * PINTU TARIK DATA: GET /api/recipes
     */
    public function getAll(array $filters = [])
    {
        $query = Recipe::with(['recipeIngredients.ingredient']);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->latest()->paginate($perPage);
    }

    /**
     * Ambil daftar resep untuk dropdown (misal di form Perencanaan Menu)
     */
    public function getAllForDropdown()
    {
        return Recipe::select('id', 'name', 'total_calorie', 'total_protein', 'total_weight')
            ->orderBy('name')
            ->get();
    }

    /**
     * Ambil detail satu resep beserta semua bahannya
     *
     * PINTU TARIK DATA: GET /api/recipes/{id}
     */
    public function findById(int $id): Recipe
    {
        return Recipe::with(['recipeIngredients.ingredient'])->findOrFail($id);
    }

    /**
     * Simpan resep baru beserta kalkulasi nutrisinya.
     *
     * PINTU MASUK DATA: POST /api/recipes
     *
     * Format $data yang diharapkan:
     * [
     *   'name'             => 'Ayam Bakar',
     *   'description'      => '...',
     *   'target_calorie'   => 3200,
     *   'target_protein'   => 100,
     *   'target_carbohydrate' => 400,
     *   'target_fat'       => 80,
     *   'ingredients' => [
     *     ['ingredient_id' => 1, 'weight_used' => 500],
     *     ['ingredient_id' => 3, 'weight_used' => 200],
     *   ]
     * ]
     *
     * @param array $data
     * @return Recipe
     */
    public function create(array $data): Recipe
    {
        // Gunakan DB transaction: jika ada yang gagal, semua di-rollback
        // Analogi NestJS: @Transaction() atau queryRunner
        return DB::transaction(function () use ($data) {

            // 1. Hitung total nutrisi dari semua bahan
            $nutritionTotals = $this->calculateTotalNutrition($data['ingredients']);

            // 2. Simpan header resep
            $recipe = Recipe::create([
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

            // 3. Simpan setiap bahan ke tabel recipe_ingredients
            $this->saveRecipeIngredients($recipe, $data['ingredients']);

            return $recipe->load('recipeIngredients.ingredient');
        });
    }

    /**
     * Update resep + recalculate semua nutrisi
     *
     * PINTU MASUK DATA: PUT /api/recipes/{id}
     */
    public function update(int $id, array $data): Recipe
    {
        return DB::transaction(function () use ($id, $data) {
            $recipe = Recipe::findOrFail($id);

            // Hapus semua bahan lama, ganti dengan yang baru
            $recipe->recipeIngredients()->delete();

            // Hitung ulang nutrisi dari bahan baru
            $nutritionTotals = $this->calculateTotalNutrition($data['ingredients']);

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

            $this->saveRecipeIngredients($recipe, $data['ingredients']);

            return $recipe->load('recipeIngredients.ingredient');
        });
    }

    /**
     * Hapus resep (soft delete)
     */
    public function delete(int $id): bool
    {
        $recipe = Recipe::findOrFail($id);

        // Cek apakah resep masih dipakai di menu aktif
        $usedInMenus = $recipe->menuItems()->count();
        if ($usedInMenus > 0) {
            throw new \Exception(
                "Resep '{$recipe->name}' masih digunakan di {$usedInMenus} menu dan tidak dapat dihapus.",
                409
            );
        }

        return $recipe->delete();
    }

    // =============================================
    // PRIVATE HELPERS
    // =============================================

    /**
     * Kalkulasi total nutrisi dari array bahan.
     * INI INTI LOGIKA KALKULASI GIZI.
     *
     * Contoh:
     * - Ayam: 100 kalori / 100gr. Dipakai 500gr → kontribusi = 500 kalori
     * - Nasi: 130 kalori / 100gr. Dipakai 200gr → kontribusi = 260 kalori
     * - Total kalori = 760 kalori
     *
     * @param array $ingredientsList [['ingredient_id' => 1, 'weight_used' => 500], ...]
     * @return array
     */
    private function calculateTotalNutrition(array $ingredientsList): array
    {
        $totals = [
            'total_calorie'      => 0,
            'total_protein'      => 0,
            'total_carbohydrate' => 0,
            'total_fat'          => 0,
            'total_weight'       => 0,
        ];

        foreach ($ingredientsList as $item) {
            $ingredient = Ingredient::findOrFail($item['ingredient_id']);
            $nutrition  = $ingredient->calculateNutritionFor($item['weight_used']);

            $totals['total_calorie']      += $nutrition['calorie'];
            $totals['total_protein']      += $nutrition['protein'];
            $totals['total_carbohydrate'] += $nutrition['carbohydrate'];
            $totals['total_fat']          += $nutrition['fat'];
            $totals['total_weight']       += $item['weight_used'];
        }

        // Bulatkan semua ke 2 desimal
        return array_map(fn($v) => round($v, 2), $totals);
    }

    /**
     * Simpan baris-baris bahan ke tabel recipe_ingredients
     *
     * @param Recipe $recipe
     * @param array  $ingredientsList
     */
    private function saveRecipeIngredients(Recipe $recipe, array $ingredientsList): void
    {
        foreach ($ingredientsList as $index => $item) {
            $ingredient = Ingredient::findOrFail($item['ingredient_id']);
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