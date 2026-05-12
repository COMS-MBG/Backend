<?php

namespace App\Services\Auth;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RBACService
{
    // Definisi lengkap roles dan permissions SOCM-MBG
    const ROLES_PERMISSIONS = [
        'super_admin' => [
            // SPPG
            'sppg.view', 'sppg.create', 'sppg.edit', 'sppg.delete',
            'sppg.assign_school', 'sppg.monitoring',
            // School
            'school.view', 'school.create', 'school.edit', 'school.delete',
            // Employee
            'employee.view', 'employee.create', 'employee.edit', 'employee.delete',
            // Finance
            'finance.view', 'finance.create', 'finance.edit', 'finance.delete',
            // Recommendation
            'recommendation.view', 'recommendation.generate', 'recommendation.approve',
            // Submission
            'submission.view', 'submission.approve', 'submission.reject',
            // Distribution
            'distribution.view',
            // Nutrition
            'nutrition.view',
            // Partner
            'partner.view', 'partner.create', 'partner.edit', 'partner.delete', 'partner.import',
        ],
        // admin-sppg = alias for the main SPPG administrator role
        'admin-sppg' => [
            'sppg.view',
            'school.view', 'school.create', 'school.edit',
            'employee.view', 'employee.create', 'employee.edit', 'employee.delete',
            'finance.view', 'finance.create', 'finance.edit',
            'nutrition.view',
            'distribution.view',
            // Partner
            'partner.view', 'partner.create', 'partner.edit', 'partner.delete', 'partner.import',
        ],
        'pemilik' => [
            'sppg.view',
            'school.view', 'school.create', 'school.edit',
            'employee.view', 'employee.create', 'employee.edit', 'employee.delete',
            'finance.view', 'finance.create', 'finance.edit',
            'nutrition.view',
            'distribution.view',
            // Partner
            'partner.view', 'partner.create', 'partner.edit', 'partner.delete', 'partner.import',
        ],
        'manajer' => [
            'sppg.view',
            'school.view', 'school.edit',
            'employee.view',
            'finance.view', 'finance.create',
            'nutrition.view',
            'distribution.view',
            // Partner
            'partner.view', 'partner.create', 'partner.edit', 'partner.import',
        ],
        'ahli_gizi' => [
            'nutrition.view', 'nutrition.create', 'nutrition.edit', 'nutrition.delete',
            'ingredient.view', 'ingredient.create', 'ingredient.edit',
        ],
        'admin_logistik' => [
            'distribution.view', 'distribution.create', 'distribution.edit',
            'courier.view', 'courier.assign',
            'delivery.view',
        ],
        'kurir' => [
            'delivery.view_own',
            'delivery.update_status',
            'delivery.upload_proof',
        ],
        'karyawan_operasional' => [
            'nutrition.view',
            'distribution.view',
        ],
    ];

    public function seedRolesAndPermissions(): void
    {
        // Reset cached roles & permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Kumpulkan semua permission unik
        $allPermissions = collect(self::ROLES_PERMISSIONS)->flatten()->unique();

        foreach ($allPermissions as $permission) {
            // 'web' guard = Sanctum SPA session guard
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Buat roles dan sync permissions
        foreach (self::ROLES_PERMISSIONS as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }
    }

    public function getPermissionsForRole(string $roleName): array
    {
        return self::ROLES_PERMISSIONS[$roleName] ?? [];
    }
}