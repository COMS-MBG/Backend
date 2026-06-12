<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('nama', 255)->nullable();
            $table->string('alamat', 1000)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('jumlah_siswa')->nullable();
            $table->string('jenjang', 50)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kota', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('kepala_sekolah', 255)->nullable();
            $table->foreignId('sppg_id')->nullable()->constrained('s_p_p_g_s')->onDelete('set null');
            $table->string('status', 50)->default('active');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['sppg_id']);
            }
            $table->dropColumn([
                'nama',
                'alamat',
                'latitude',
                'longitude',
                'jumlah_siswa',
                'jenjang',
                'kecamatan',
                'kota',
                'provinsi',
                'telepon',
                'kepala_sekolah',
                'sppg_id',
                'status',
                'deleted_at'
            ]);
        });
    }
};
