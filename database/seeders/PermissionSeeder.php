<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Seed permissions sesuai modul aplikasi.
     *
     * Format slug: "module.action"
     * Contoh: "employee.create", "nutrition.read"
     */
    public function run(): void
    {
        $permissions = [
            // Employee
            ['module' => 'employee',     'feature' => 'employee',     'action' => 'create'],
            ['module' => 'employee',     'feature' => 'employee',     'action' => 'read'],
            ['module' => 'employee',     'feature' => 'employee',     'action' => 'update'],
            ['module' => 'employee',     'feature' => 'employee',     'action' => 'delete'],

            // School
            ['module' => 'school',       'feature' => 'school',       'action' => 'create'],
            ['module' => 'school',       'feature' => 'school',       'action' => 'read'],
            ['module' => 'school',       'feature' => 'school',       'action' => 'update'],
            ['module' => 'school',       'feature' => 'school',       'action' => 'delete'],

            // SPPG
            ['module' => 'sppg',         'feature' => 'sppg',         'action' => 'create'],
            ['module' => 'sppg',         'feature' => 'sppg',         'action' => 'read'],
            ['module' => 'sppg',         'feature' => 'sppg',         'action' => 'update'],
            ['module' => 'sppg',         'feature' => 'sppg',         'action' => 'delete'],

            // Nutrition — Ingredients
            ['module' => 'nutrition',    'feature' => 'ingredients',  'action' => 'create'],
            ['module' => 'nutrition',    'feature' => 'ingredients',  'action' => 'read'],
            ['module' => 'nutrition',    'feature' => 'ingredients',  'action' => 'update'],
            ['module' => 'nutrition',    'feature' => 'ingredients',  'action' => 'delete'],

            // Nutrition — Recipes
            ['module' => 'nutrition',    'feature' => 'recipes',      'action' => 'create'],
            ['module' => 'nutrition',    'feature' => 'recipes',      'action' => 'read'],
            ['module' => 'nutrition',    'feature' => 'recipes',      'action' => 'update'],
            ['module' => 'nutrition',    'feature' => 'recipes',      'action' => 'delete'],

            // Nutrition — Menus
            ['module' => 'nutrition',    'feature' => 'menus',        'action' => 'create'],
            ['module' => 'nutrition',    'feature' => 'menus',        'action' => 'read'],
            ['module' => 'nutrition',    'feature' => 'menus',        'action' => 'update'],
            ['module' => 'nutrition',    'feature' => 'menus',        'action' => 'delete'],

            // Distribution
            ['module' => 'distribution', 'feature' => 'distribution', 'action' => 'create'],
            ['module' => 'distribution', 'feature' => 'distribution', 'action' => 'read'],
            ['module' => 'distribution', 'feature' => 'distribution', 'action' => 'update'],
            ['module' => 'distribution', 'feature' => 'distribution', 'action' => 'delete'],

            // Finance
            ['module' => 'finance',      'feature' => 'finance',      'action' => 'create'],
            ['module' => 'finance',      'feature' => 'finance',      'action' => 'read'],
            ['module' => 'finance',      'feature' => 'finance',      'action' => 'update'],
            ['module' => 'finance',      'feature' => 'finance',      'action' => 'delete'],
        ];

        foreach ($permissions as $p) {
            $slug = "{$p['module']}.{$p['action']}";
            // Jika ada feature berbeda di module yang sama, tambahkan feature ke slug
            if ($p['feature'] !== $p['module']) {
                $slug = "{$p['feature']}.{$p['action']}";
            }

            Permission::firstOrCreate(
                ['slug' => $slug],
                [
                    'name'    => ucfirst($p['action']) . ' ' . ucfirst(str_replace('_', ' ', $p['feature'])),
                    'slug'    => $slug,
                    'module'  => $p['module'],
                    'feature' => $p['feature'],
                    'action'  => $p['action'],
                ]
            );
        }

        $this->command->info('Permissions seeded: ' . Permission::count() . ' permissions.');
    }
}
