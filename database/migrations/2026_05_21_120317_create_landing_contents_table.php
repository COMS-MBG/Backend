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
    Schema::create('landing_contents', function (Blueprint $table) {
        $table->id();
        // Kolom penanda bagian landing page (contoh: 'hero', 'transparency')
        $table->string('section_name')->unique(); 
        
        $table->string('title')->nullable();
        $table->text('description')->nullable();
        $table->string('image_path')->nullable();
        
        // Status apakah konten ini mau ditampilkan atau disembunyikan
        $table->boolean('is_active')->default(true); 
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_contents');
    }
};
