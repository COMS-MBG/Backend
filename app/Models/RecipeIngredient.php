<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * MODEL untuk tabel pivot 'recipe_ingredients'.
 * Ini bukan hanya pivot biasa karena punya kolom extra (weight_used, kontribusi nutrisi).
 *
 * Analogi NestJS: Entity junction table dengan @ManyToOne ke dua sisi + extra kolom.
 */
class RecipeIngredient extends Model
{
    use HasFactory;

    protected $table = 'recipe_ingredients';

    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'weight_used',
        'calorie_contribution',
        'protein_contribution',
        'carbohydrate_contribution',
        'fat_contribution',
        'order',
    ];

    protected $casts = [
        'weight_used'              => 'float',
        'calorie_contribution'     => 'float',
        'protein_contribution'     => 'float',
        'carbohydrate_contribution'=> 'float',
        'fat_contribution'         => 'float',
        'order'                    => 'integer',
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================

    /**
     * Baris ini milik resep mana?
     */
    public function recipe()
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    /**
     * Baris ini mengacu ke ingredient mana?
     */
    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }
}