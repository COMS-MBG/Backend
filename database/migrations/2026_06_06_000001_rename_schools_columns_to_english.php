<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rename all Indonesian column names in 'schools' table to English.
 *
 * Mapping:
 *   nama          → name
 *   alamat        → address
 *   jumlah_siswa  → student_count
 *   jenjang       → school_level
 *   kecamatan     → district
 *   kota          → city
 *   provinsi      → province
 *   telepon       → phone
 *   kepala_sekolah → principal
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->renameColumn('nama',           'name');
            $table->renameColumn('alamat',         'address');
            $table->renameColumn('jumlah_siswa',   'student_count');
            $table->renameColumn('jenjang',        'school_level');
            $table->renameColumn('kecamatan',      'district');
            $table->renameColumn('kota',           'city');
            $table->renameColumn('provinsi',       'province');
            $table->renameColumn('telepon',        'phone');
            $table->renameColumn('kepala_sekolah', 'principal');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->renameColumn('name',          'nama');
            $table->renameColumn('address',       'alamat');
            $table->renameColumn('student_count', 'jumlah_siswa');
            $table->renameColumn('school_level',  'jenjang');
            $table->renameColumn('district',      'kecamatan');
            $table->renameColumn('city',          'kota');
            $table->renameColumn('province',      'provinsi');
            $table->renameColumn('phone',         'telepon');
            $table->renameColumn('principal',     'kepala_sekolah');
        });
    }
};
