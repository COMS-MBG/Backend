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
        Schema::create('stock_minimum', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sppg_id')->constrained('s_p_p_g_s')->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained('ingredients')->onDelete('cascade');
            $table->decimal('minimum_quantity', 10, 3);
            $table->string('unit', 20); // enum(kg,liter,gram,ml,pcs)
            $table->timestamps();

            $table->unique(['sppg_id', 'ingredient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_minimum');
    }
};
