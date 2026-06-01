<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rename partners table columns from Indonesian to English.
 *
 * Mapping:
 *   nama_sekolah   → school_name
 *   bentuk         → school_type
 *   status         → ownership_status  (values: public / private)
 *   alamat         → address
 *   kecamatan      → district
 *   kabupaten_kota → city
 *   jumlah_porsi   → portion_count
 *
 * Also updates index names to match new column names.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            // Drop old indexes before renaming columns
            $table->dropIndex(['bentuk']);
            $table->dropIndex(['status']);
            $table->dropIndex(['kecamatan']);
            $table->dropIndex(['kabupaten_kota']);

            // Rename columns
            $table->renameColumn('nama_sekolah',   'school_name');
            $table->renameColumn('bentuk',          'school_type');
            $table->renameColumn('status',          'ownership_status');
            $table->renameColumn('alamat',          'address');
            $table->renameColumn('kecamatan',       'district');
            $table->renameColumn('kabupaten_kota',  'city');
            $table->renameColumn('jumlah_porsi',    'portion_count');
        });

        // Re-create indexes with new column names
        Schema::table('partners', function (Blueprint $table) {
            $table->index('school_type');
            $table->index('ownership_status');
            $table->index('district');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropIndex(['school_type']);
            $table->dropIndex(['ownership_status']);
            $table->dropIndex(['district']);
            $table->dropIndex(['city']);

            $table->renameColumn('school_name',      'nama_sekolah');
            $table->renameColumn('school_type',      'bentuk');
            $table->renameColumn('ownership_status', 'status');
            $table->renameColumn('address',          'alamat');
            $table->renameColumn('district',         'kecamatan');
            $table->renameColumn('city',             'kabupaten_kota');
            $table->renameColumn('portion_count',    'jumlah_porsi');
        });

        Schema::table('partners', function (Blueprint $table) {
            $table->index('bentuk');
            $table->index('status');
            $table->index('kecamatan');
            $table->index('kabupaten_kota');
        });
    }
};
