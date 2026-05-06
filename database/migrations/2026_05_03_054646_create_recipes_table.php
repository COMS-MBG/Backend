<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();

            // =========================
            // BASIC INFO
            // =========================
            $table->string('name');
            $table->text('description')->nullable();

            // =========================
            // TARGET NUTRITION (INPUT)
            // =========================
            $table->decimal('target_calorie', 10, 2)->default(0);
            $table->decimal('target_protein', 10, 2)->default(0);
            $table->decimal('target_carbohydrate', 10, 2)->default(0);
            $table->decimal('target_fat', 10, 2)->default(0);

            // =========================
            // TOTAL NUTRITION (RESULT CALC)
            // =========================
            $table->decimal('total_calorie', 10, 2)->default(0);
            $table->decimal('total_protein', 10, 2)->default(0);
            $table->decimal('total_carbohydrate', 10, 2)->default(0);
            $table->decimal('total_fat', 10, 2)->default(0);
            $table->decimal('total_weight', 10, 2)->default(0);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};