<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('carbohydrate', 8, 2)->default(0);
            $table->decimal('protein', 8, 2)->default(0);
            $table->decimal('calorie', 8, 2)->default(0);
            $table->decimal('fat', 8, 2)->default(0);
            $table->decimal('serving_weight', 8, 2)->default(100);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
