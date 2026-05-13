<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed roles dan assign permissions ke masing-masing role.
     *
     * Arsitektur:
     *   - Role di-assign ke Employee via employees.role_id
     *   - Bukan ke User (tidak pakai Spatie)
     *   - Super admin tidak perlu role di sini (pakai users.role_type)
     */
    public function run(): void
    {
        // ── Definisi role dan permission slugs ────────────────────────────────

        $rolesConfig = [
            'SPPG Admin' => [
                'slug'        => 'admin-sppg',
                'description' => 'Admin penuh SPPG — akses semua modul',
                'permissions' => '*', // semua permission
            ],
            'Ahli Gizi' => [
                'slug'        => 'ahli-gizi',
                'description' => 'Mengelola bahan baku, resep, dan menu',
                'permissions' => [
                    'ingredients.create', 'ingredients.read', 'ingredients.update', 'ingredients.delete',
                    'recipes.create', 'recipes.read', 'recipes.update', 'recipes.delete',
                    'menus.create', 'menus.read', 'menus.update', 'menus.delete',
                ],
            ],
            'Admin Logistik' => [
                'slug'        => 'admin-logistik',
                'description' => 'Mengelola distribusi dan pengiriman',
                'permissions' => [
                    'distribution.create', 'distribution.read', 'distribution.update', 'distribution.delete',
                ],
            ],
            'Kurir' => [
                'slug'        => 'kurir',
                'description' => 'Melakukan pengiriman dan update status',
                'permissions' => [
                    'distribution.read', 'distribution.update',
                ],
            ],
            'Pemilik' => [
                'slug'        => 'pemilik',
                'description' => 'Pemilik SPPG — akses semua modul + keuangan',
                'permissions' => '*',
            ],
            'Manajer' => [
                'slug'        => 'manajer',
                'description' => 'Manajer operasional SPPG',
                'permissions' => [
                    'employee.read',
                    'school.read', 'school.update',
                    'ingredients.read', 'recipes.read', 'menus.read',
                    'distribution.read',
                    'finance.read', 'finance.create',
                ],
            ],
        ];

        // ── Buat role dan sync permissions ────────────────────────────────────

        foreach ($rolesConfig as $name => $config) {
            $role = Role::firstOrCreate(
                ['slug' => $config['slug']],
                [
                    'name'        => $name,
                    'slug'        => $config['slug'],
                    'description' => $config['description'],
                ]
            );

            // Sync permissions
            if ($config['permissions'] === '*') {
                $permissionIds = Permission::pluck('id')->toArray();
            } else {
                $permissionIds = Permission::whereIn('slug', $config['permissions'])
                    ->pluck('id')
                    ->toArray();
            }

            $role->permissions()->sync($permissionIds);

            $this->command->info("Role '{$name}' — {$role->permissions()->count()} permissions");
        }
    }
}
