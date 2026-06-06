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
        Schema::create('sppg_draft_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draft_id')->constrained('sppg_drafts')->onDelete('cascade');
            $table->string('school_name', 255);
            $table->string('npsn', 20)->nullable();
            $table->string('level', 20); // SD, SMP, SMA, SMK
            $table->string('school_status', 20); // negeri, swasta
            $table->text('address');
            $table->string('city', 100);
            $table->string('district', 100);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('jumlah_porsi');
            $table->string('data_source', 30); // database, openstreetmap
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sppg_draft_partners');
    }
};
