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
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // Nama pemberi ulasan
            $table->string('role')->nullable();           // Peran: Wali Murid, dll
            $table->text('message');                      // Isi ulasan
            $table->unsignedTinyInteger('rating')->default(5); // Skor 1-5
            $table->boolean('is_approved')->default(false); // Status moderasi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
