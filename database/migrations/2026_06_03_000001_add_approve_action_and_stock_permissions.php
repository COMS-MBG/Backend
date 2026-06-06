<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tahap 1 — Tambah 'approve' ke enum action + 5 permission stok baru.
     *
     * Permission stok diperlukan oleh Tahap 2 (Modul Stok),
     * tapi slug-nya harus ada sejak Tahap 1 agar seeder role bisa assign.
     */
    public function up(): void
    {
        // 1. Alter enum action — tambah 'approve'
        $connection = DB::connection()->getDriverName();
        if ($connection === 'pgsql') {
            DB::statement("ALTER TABLE permissions DROP CONSTRAINT IF EXISTS permissions_action_check");
            DB::statement("ALTER TABLE permissions ADD CONSTRAINT permissions_action_check CHECK (action IN ('create', 'read', 'update', 'delete', 'approve'))");
        } elseif ($connection === 'sqlite') {
            // SQLite does not support MODIFY COLUMN or strict ENUM alter table
        } else {
            DB::statement("ALTER TABLE permissions MODIFY COLUMN `action` ENUM('create','read','update','delete','approve') NULL");
        }

        // 2. Insert 5 permission stok baru
        $stockPermissions = [
            ['name' => 'Read Stock',    'slug' => 'stock.read',    'module' => 'stock', 'feature' => 'stock', 'action' => 'read'],
            ['name' => 'Create Stock',  'slug' => 'stock.create',  'module' => 'stock', 'feature' => 'stock', 'action' => 'create'],
            ['name' => 'Update Stock',  'slug' => 'stock.update',  'module' => 'stock', 'feature' => 'stock', 'action' => 'update'],
            ['name' => 'Delete Stock',  'slug' => 'stock.delete',  'module' => 'stock', 'feature' => 'stock', 'action' => 'delete'],
            ['name' => 'Approve Stock', 'slug' => 'stock.approve', 'module' => 'stock', 'feature' => 'stock', 'action' => 'approve'],
        ];

        $now = now();
        foreach ($stockPermissions as $perm) {
            DB::table('permissions')->insertOrIgnore(array_merge($perm, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        // Remove stock permissions
        DB::table('permissions')->where('module', 'stock')->delete();

        // Revert enum
        $connection = DB::connection()->getDriverName();
        if ($connection === 'pgsql') {
            DB::statement("ALTER TABLE permissions DROP CONSTRAINT IF EXISTS permissions_action_check");
            DB::statement("ALTER TABLE permissions ADD CONSTRAINT permissions_action_check CHECK (action IN ('create', 'read', 'update', 'delete'))");
        } elseif ($connection === 'sqlite') {
            // SQLite does not support MODIFY COLUMN or strict ENUM alter table
        } else {
            DB::statement("ALTER TABLE permissions MODIFY COLUMN `action` ENUM('create','read','update','delete') NULL");
        }
    }
};
