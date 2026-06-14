<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * MODEL = Representasi tabel 'ingredients' di PHP.
 * Analogi NestJS: *.entity.ts (TypeORM Entity)
 *
 * Model ini otomatis bisa:
 * - Ingredient::all()           → SELECT * FROM ingredients
 * - Ingredient::find($id)       → SELECT * WHERE id = ?
 * - Ingredient::create([...])   → INSERT INTO ingredients
 * - $ingredient->update([...])  → UPDATE ingredients WHERE id = ?
 * - $ingredient->delete()       → DELETE FROM ingredients WHERE id = ?
 */
class Ingredient extends Model
{
    use HasFactory;

    protected $table = 'ingredients';

    /**
     * $fillable = kolom yang boleh diisi via mass assignment (create/update)
     * Analogi NestJS: @Column() di entity + DTO whitelist
     */
    protected $fillable = [
        'sppg_id',
        'name',
        'carbohydrate',
        'protein',
        'calorie',
        'fat',
        'serving_weight',
        'description',
    ];

    /**
     * Cast otomatis tipe data saat diambil dari DB
     */
    protected $casts = [
        'carbohydrate'   => 'float',
        'protein'        => 'float',
        'calorie'        => 'float',
        'fat'            => 'float',
        'serving_weight' => 'float',
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================

    /**
     * Satu ingredient bisa dipakai di banyak recipe_ingredients
     * Analogi NestJS: @OneToMany(() => RecipeIngredient, ri => ri.ingredient)
     */
    public function recipeIngredients()
    {
        return $this->hasMany(RecipeIngredient::class, 'ingredient_id');
    }

    /**
     * Ingredient ini dipakai di resep-resep mana saja (via pivot)
     */
    public function recipes()
    {
        return $this->belongsToMany(Recipe::class, 'recipe_ingredients')
            ->withPivot(['weight_used', 'calorie_contribution', 'protein_contribution',
                         'carbohydrate_contribution', 'fat_contribution', 'order'])
            ->withTimestamps();
    }

    // =============================================
    // HELPER / ACCESSOR
    // =============================================

    /**
     * Hitung nilai nutrisi untuk berat tertentu.
     * Dipakai oleh RecipeService saat menghitung kontribusi bahan.
     *
     * @param float $weightGram  Berat yang dipakai (gram)
     * @return array
     */
    public function calculateNutritionFor(float $weightGram): array
    {
        $ratio = $weightGram / $this->serving_weight;

        return [
            'calorie'        => round($this->calorie * $ratio, 2),
            'protein'        => round($this->protein * $ratio, 2),
            'carbohydrate'   => round($this->carbohydrate * $ratio, 2),
            'fat'            => round($this->fat * $ratio, 2),
        ];
    }
}