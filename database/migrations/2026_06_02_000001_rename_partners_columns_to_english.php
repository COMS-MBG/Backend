<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('partners', 'nama_sekolah')) {
            Schema::table('partners', function (Blueprint $table) {
                try { $table->dropIndex(['bentuk']); } catch (\Exception $e) {}
                try { $table->dropIndex(['status']); } catch (\Exception $e) {}
                try { $table->dropIndex(['kecamatan']); } catch (\Exception $e) {}
                try { $table->dropIndex(['kabupaten_kota']); } catch (\Exception $e) {}

                $table->renameColumn('nama_sekolah',  'school_name');
                $table->renameColumn('bentuk',         'school_type');
                $table->renameColumn('status',         'ownership_status');
                $table->renameColumn('alamat',         'address');
                $table->renameColumn('kecamatan',      'district');
                $table->renameColumn('kabupaten_kota', 'city');
                $table->renameColumn('jumlah_porsi',   'portion_count');
            });

            Schema::table('partners', function (Blueprint $table) {
                $table->index('school_type');
                $table->index('ownership_status');
                $table->index('district');
                $table->index('city');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('partners', 'school_name')) {
            Schema::table('partners', function (Blueprint $table) {
                try { $table->dropIndex(['school_type']); } catch (\Exception $e) {}
                try { $table->dropIndex(['ownership_status']); } catch (\Exception $e) {}
                try { $table->dropIndex(['district']); } catch (\Exception $e) {}
                try { $table->dropIndex(['city']); } catch (\Exception $e) {}
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
    }
};
