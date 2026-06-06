<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add report.update permission if it doesn't exist
        $exists = DB::table('permissions')->where('slug', 'report.update')->exists();
        if (!$exists) {
            DB::table('permissions')->insert([
                'name'       => 'Update Report',
                'slug'       => 'report.update',
                'module'     => 'report',
                'feature'    => 'shipping_rate',
                'action'     => 'update',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Grant report.update to all roles that currently have report.read
        // (i.e. sppg_admin roles which have * permissions get it automatically via sync)
        // For logistics_admin specifically — they manage shipping rates
        $permission = DB::table('permissions')->where('slug', 'report.update')->first();
        if (!$permission) return;

        // Find all logistics_admin roles across all SPPGs
        $logisticsRoles = DB::table('roles')
            ->where('slug', 'logistics_admin')
            ->pluck('id');

        foreach ($logisticsRoles as $roleId) {
            $alreadyLinked = DB::table('role_permission')
                ->where('role_id', $roleId)
                ->where('permission_id', $permission->id)
                ->exists();

            if (!$alreadyLinked) {
                DB::table('role_permission')->insert([
                    'role_id'       => $roleId,
                    'permission_id' => $permission->id,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('permissions')->where('slug', 'report.update')->delete();
    }
};
