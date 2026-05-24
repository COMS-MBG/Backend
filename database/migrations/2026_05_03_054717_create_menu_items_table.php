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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();

            // Foreign key ke tabel menus
            $table->foreignId('menu_id')
                ->constrained('menus')
                ->onDelete('cascade');

            // Foreign key ke tabel recipes
            $table->foreignId('recipe_id')
                ->constrained('recipes')
                ->onDelete('restrict');

            // Hari dalam seminggu: 1=Senin, 2=Selasa, ..., 4=Kamis
            $table->unsignedTinyInteger('day_of_week');

            // Tanggal spesifik menu item ini
            $table->date('menu_date')->nullable();

            // Urutan tampil dalam satu hari
            $table->unsignedInteger('order')->default(1);

            $table->timestamps();

            // Index untuk query cepat
            $table->index(['menu_id', 'day_of_week']);
            $table->index('recipe_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
