<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Update Indonesian values to English for:
 *   - employees.position values
 *   - roles.slug values
 *
 * Position mapping:
 *   pemilik              → owner
 *   manajer              → manager
 *   ahli_gizi            → nutritionist
 *   admin_logistik       → logistics_admin
 *   kurir                → courier
 *   karyawan_operasional → operational_staff
 *
 * Role slug mapping:
 *   pemilik        → owner
 *   manajer        → manager
 *   ahli-gizi      → nutritionist
 *   admin-logistik → logistics_admin
 *   admin-sppg     → sppg_admin
 *   kurir          → courier
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Update employees.position values ──────────────────────────────────
        $positionMap = [
            'pemilik'              => 'owner',
            'manajer'              => 'manager',
            'ahli_gizi'            => 'nutritionist',
            'admin_logistik'       => 'logistics_admin',
            'kurir'                => 'courier',
            'karyawan_operasional' => 'operational_staff',
        ];

        foreach ($positionMap as $old => $new) {
            DB::table('employees')
                ->where('position', $old)
                ->update(['position' => $new]);
        }

        // ── Update roles.slug values ──────────────────────────────────────────
        $slugMap = [
            'pemilik'        => 'owner',
            'manajer'        => 'manager',
            'ahli-gizi'      => 'nutritionist',
            'admin-logistik' => 'logistics_admin',
            'admin-sppg'     => 'sppg_admin',
            'kurir'          => 'courier',
        ];

        foreach ($slugMap as $old => $new) {
            DB::table('roles')
                ->where('slug', $old)
                ->update(['slug' => $new]);
        }
    }

    public function down(): void
    {
        // ── Revert employees.position values ──────────────────────────────────
        $positionMap = [
            'owner'            => 'pemilik',
            'manager'          => 'manajer',
            'nutritionist'     => 'ahli_gizi',
            'logistics_admin'  => 'admin_logistik',
            'courier'          => 'kurir',
            'operational_staff'=> 'karyawan_operasional',
        ];

        foreach ($positionMap as $old => $new) {
            DB::table('employees')
                ->where('position', $old)
                ->update(['position' => $new]);
        }

        // ── Revert roles.slug values ──────────────────────────────────────────
        $slugMap = [
            'owner'          => 'pemilik',
            'manager'        => 'manajer',
            'nutritionist'   => 'ahli-gizi',
            'logistics_admin'=> 'admin-logistik',
            'sppg_admin'     => 'admin-sppg',
            'courier'        => 'kurir',
        ];

        foreach ($slugMap as $old => $new) {
            DB::table('roles')
                ->where('slug', $old)
                ->update(['slug' => $new]);
        }
    }
};
