<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['module' => 'dashboard',     'feature' => 'dashboard',     'action' => 'read'],

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

            // Nutrition — Module-level
            ['module' => 'nutrition',    'feature' => 'nutrition',    'action' => 'create'],
            ['module' => 'nutrition',    'feature' => 'nutrition',    'action' => 'read'],
            ['module' => 'nutrition',    'feature' => 'nutrition',    'action' => 'update'],
            ['module' => 'nutrition',    'feature' => 'nutrition',    'action' => 'delete'],

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

            // Partner
            ['module' => 'partner',      'feature' => 'partner',      'action' => 'create'],
            ['module' => 'partner',      'feature' => 'partner',      'action' => 'read'],
            ['module' => 'partner',      'feature' => 'partner',      'action' => 'update'],
            ['module' => 'partner',      'feature' => 'partner',      'action' => 'delete'],

            // Report
            ['module' => 'report',       'feature' => 'report',       'action' => 'create'],
            ['module' => 'report',       'feature' => 'report',       'action' => 'read'],
            ['module' => 'report',       'feature' => 'report',       'action' => 'update'],
            ['module' => 'report',       'feature' => 'report',       'action' => 'delete'],
        ];

        foreach ($permissions as $p) {
            $slug = "{$p['module']}.{$p['action']}";
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
