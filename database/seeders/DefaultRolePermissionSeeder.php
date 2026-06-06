<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\SPPG;
use Illuminate\Database\Seeder;

class DefaultRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $sppgs = SPPG::all();
        foreach ($sppgs as $sppg) {
            $this->seedForSppg($sppg->id);
        }
    }

    public function seedForSppg(int|string $sppgId): void
    {
        $rolesConfig = [
            'SPPG Admin' => [
                'slug'        => 'sppg_admin',
                'description' => 'Full SPPG admin — access to all modules',
                'permissions' => '*',
            ],
            'Nutritionist' => [
                'slug'        => 'nutritionist',
                'description' => 'Manages raw ingredients, recipes, and menus',
                'permissions' => [
                    'dashboard.read',
                    'ingredients.read', 'ingredients.create', 'ingredients.update', 'ingredients.delete',
                    'recipes.read', 'recipes.create', 'recipes.update', 'recipes.delete',
                    'menus.read', 'menus.create', 'menus.update', 'menus.delete',
                    'stock.read',
                    'report.read',
                ],
            ],
            'Logistics Admin' => [
                'slug'        => 'logistics_admin',
                'description' => 'Manages logistics, stock, and distribution',
                'permissions' => [
                    'dashboard.read',
                    'stock.read', 'stock.create', 'stock.update', 'stock.delete', 'stock.approve',
                    'distribution.read', 'distribution.create', 'distribution.update', 'distribution.delete',
                    'report.read',
                ],
            ],
            'Courier' => [
                'slug'        => 'courier',
                'description' => 'Delivery courier — updates location and delivery status',
                'permissions' => [
                    'dashboard.read',
                    'distribution.read',
                    'distribution.update',
                ],
            ],
        ];

        foreach ($rolesConfig as $name => $config) {
            $role = Role::updateOrCreate(
                [
                    'sppg_id' => $sppgId,
                    'slug'    => $config['slug']
                ],
                [
                    'name'        => $name,
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
        }
    }
}
