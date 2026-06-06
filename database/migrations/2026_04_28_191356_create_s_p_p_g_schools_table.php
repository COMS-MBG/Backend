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
        Schema::create('sppg_schools', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('sppg_id');
            $table->unsignedBigInteger('school_id');
            $table->date('tanggal_bergabung')->nullable();
            $table->string('status', 50)->default('aktif');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('sppg_id')->references('id')->on('s_p_p_g_s')->onDelete('cascade');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sppg_schools');
    }
};
