<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\MenuItem;

/**
 * MODEL untuk tabel 'recipes'.
 * Satu Recipe punya banyak RecipeIngredient (bahan-bahannya).
 */
class Recipe extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recipes';

    protected $fillable = [
        'sppg_id',
        'name',
        'description',
        'target_calorie',
        'target_protein',
        'target_carbohydrate',
        'target_fat',
        'total_calorie',
        'total_protein',
        'total_carbohydrate',
        'total_fat',
        'total_weight',
    ];

    protected $casts = [
        'target_calorie'       => 'float',
        'target_protein'       => 'float',
        'target_carbohydrate'  => 'float',
        'target_fat'           => 'float',
        'total_calorie'        => 'float',
        'total_protein'        => 'float',
        'total_carbohydrate'   => 'float',
        'total_fat'            => 'float',
        'total_weight'         => 'float',
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================

    /**
     * Resep ini punya bahan-bahan apa saja (via tabel recipe_ingredients)
     * Analogi NestJS: @OneToMany(() => RecipeIngredient, ri => ri.recipe)
     */
    public function recipeIngredients()
    {
        return $this->hasMany(RecipeIngredient::class, 'recipe_id')
            ->orderBy('order');
    }

    /**
     * Shortcut: langsung ke Ingredient-nya via pivot
     */
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')
            ->withPivot(['weight_used', 'calorie_contribution', 'protein_contribution',
                         'carbohydrate_contribution', 'fat_contribution', 'order'])
            ->withTimestamps()
            ->orderByPivot('order');
    }

    /**
     * Resep ini dipakai di menu items mana saja
     */
    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'recipe_id');
    }

    // =============================================
    // ACCESSOR
    // =============================================

    /**
     * Persentase pencapaian kalori dari target
     * Bisa dipanggil: $recipe->calorie_achievement_percentage
     */
    public function getCalorieAchievementPercentageAttribute(): float
    {
        if ($this->target_calorie <= 0) return 0;
        return round(($this->total_calorie / $this->target_calorie) * 100, 1);
    }
}