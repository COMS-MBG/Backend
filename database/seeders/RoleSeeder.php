<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $rolesConfig = [
            'SPPG Admin' => [
                'slug'        => 'admin-sppg',
                'description' => 'Admin penuh SPPG — akses semua modul',
                'permissions' => '*',
            ],
            'Pemilik' => [
                'slug'        => 'pemilik',
                'description' => 'Pemilik SPPG — akses semua modul + keuangan',
                'permissions' => '*',
            ],
            'Ahli Gizi' => [
                'slug'        => 'ahli-gizi',
                'description' => 'Mengelola bahan baku, resep, dan menu',
                'permissions' => [
                    'dashboard.read',
                    'nutrition.create', 'nutrition.read', 'nutrition.update', 'nutrition.delete',
                    'ingredients.create', 'ingredients.read', 'ingredients.update', 'ingredients.delete',
                    'recipes.create', 'recipes.read', 'recipes.update', 'recipes.delete',
                    'menus.create', 'menus.read', 'menus.update', 'menus.delete',
                    'report.read',
                ],
            ],
            'Admin Logistik' => [
                'slug'        => 'admin-logistik',
                'description' => 'Mengelola distribusi dan pengiriman',
                'permissions' => [
                    'dashboard.read',
                    'distribution.create', 'distribution.read', 'distribution.update', 'distribution.delete',
                    'partner.read',
                    'report.read', 'report.update',
                ],
            ],
            'Kurir' => [
                'slug'        => 'kurir',
                'description' => 'Melakukan pengiriman dan update status',
                'permissions' => [
                    'dashboard.read',
                    'distribution.read', 'distribution.update',
                ],
            ],
            'Manajer' => [
                'slug'        => 'manajer',
                'description' => 'Manajer operasional SPPG',
                'permissions' => [
                    'dashboard.read',
                    'employee.read',
                    'school.read', 'school.update',
                    'nutrition.read',
                    'ingredients.read', 'recipes.read', 'menus.read',
                    'distribution.read',
                    'finance.read', 'finance.create',
                    'partner.read', 'partner.update',
                    'report.read',
                ],
            ],
        ];

        foreach ($rolesConfig as $name => $config) {
            $role = Role::firstOrCreate(
                ['slug' => $config['slug']],
                [
                    'name'        => $name,
                    'slug'        => $config['slug'],
                    'description' => $config['description'],
                ]
            );

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
