<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_sekolah');
            $table->string('npsn')->nullable()->unique();
            $table->string('bentuk', 50);           // SMA, SMK, MA, dll
            $table->string('status', 50);            // Negeri, Swasta
            $table->text('alamat')->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kabupaten_kota', 100)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('jumlah_porsi')->default(0);
            $table->uuid('sppg_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            // ── Indexes for frequent queries ──
            $table->index('bentuk');
            $table->index('status');
            $table->index('kecamatan');
            $table->index('kabupaten_kota');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
