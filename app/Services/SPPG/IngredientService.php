<?php

namespace App\Services\SPPG;

use App\Models\Ingredient;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * SERVICE = Tempat semua logic bisnis untuk fitur Ingredient.
 * Analogi NestJS: IngredientService (*.service.ts)
 *
 * Controller hanya memanggil service ini.
 * Service yang tahu cara baca/tulis ke database via Model.
 */
class IngredientService
{
    /**
     * Ambil semua ingredient (dengan paginasi & filter nama)
     *
     * PINTU TARIK DATA: Dipanggil saat FE GET /api/ingredients
     *
     * @param array $filters  ['search' => 'ayam', 'per_page' => 15]
     * @return LengthAwarePaginator
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Ingredient::query();

        // Filter pencarian nama
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->latest()->paginate($perPage);
    }

    /**
     * Ambil semua ingredient tanpa paginasi (untuk dropdown di form resep)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllForDropdown()
    {
        return Ingredient::select('id', 'name', 'calorie', 'protein', 'carbohydrate', 'fat', 'serving_weight')
            ->orderBy('name')
            ->get();
    }

    /**
     * Ambil satu ingredient berdasarkan ID
     *
     * @param int $id
     * @return Ingredient
     */
    public function findById(int $id): Ingredient
    {
        return Ingredient::findOrFail($id);
        // findOrFail otomatis lempar 404 jika tidak ditemukan
    }

    /**
     * Simpan ingredient baru ke database
     *
     * PINTU MASUK DATA: Dipanggil saat FE POST /api/ingredients
     *
     * @param array $data Data yang sudah divalidasi dari Request
     * @return Ingredient
     */
    public function create(array $data): Ingredient
    {
        return Ingredient::create($data);
    }

    /**
     * Update ingredient yang sudah ada
     *
     * PINTU MASUK DATA: Dipanggil saat FE PUT /api/ingredients/{id}
     *
     * @param int   $id
     * @param array $data
     * @return Ingredient
     */
    public function update(int $id, array $data): Ingredient
    {
        $ingredient = $this->findById($id);
        $ingredient->update($data);

        return $ingredient->fresh(); // fresh() = reload dari DB setelah update
    }

    /**
     * Soft delete ingredient
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $ingredient = $this->findById($id);

        // Cek apakah ingredient masih dipakai di resep aktif
        $usedInRecipes = $ingredient->recipeIngredients()->count();
        if ($usedInRecipes > 0) {
            throw new \Exception(
                "Ingredient '{$ingredient->name}' masih digunakan di {$usedInRecipes} resep dan tidak dapat dihapus.",
                409
            );
        }

        return $ingredient->delete();
    }

    /**
     * Hitung preview nutrisi untuk berat tertentu (tanpa save ke DB)
     * Dipakai FE untuk live preview saat user ketik berat di form resep
     *
     * @param int   $ingredientId
     * @param float $weight
     * @return array
     */
    public function calculateNutritionPreview(int $ingredientId, float $weight): array
    {
        $ingredient = $this->findById($ingredientId);
        $nutrition  = $ingredient->calculateNutritionFor($weight);

        return [
            'ingredient_id'   => $ingredient->id,
            'ingredient_name' => $ingredient->name,
            'weight_used'     => $weight,
            'nutrition'       => $nutrition,
        ];
    }
}