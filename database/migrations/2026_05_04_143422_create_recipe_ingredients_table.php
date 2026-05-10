<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();

            // 🔥 RELATION
            $table->foreignId('recipe_id')
                ->constrained('recipes')
                ->onDelete('cascade');

            $table->foreignId('ingredient_id')
                ->constrained('ingredients')
                ->onDelete('cascade');

            // 🔥 INPUT
            $table->decimal('weight_used', 10, 2);

            // 🔥 HASIL HITUNG NUTRISI
            $table->decimal('calorie_contribution', 10, 2)->default(0);
            $table->decimal('protein_contribution', 10, 2)->default(0);
            $table->decimal('carbohydrate_contribution', 10, 2)->default(0);
            $table->decimal('fat_contribution', 10, 2)->default(0);

            // urutan bahan
            $table->integer('order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
    }
};