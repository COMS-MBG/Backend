<?php

namespace App\Services\SPPG;

use App\Models\SPPG;
use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\Partner;
use App\Mail\AccountCreatedMail;
use Database\Seeders\DefaultRolePermissionSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SppgRegistrationService
{
    /**
     * Register a new SPPG with its admin user, optional staff members, and partners.
     *
     * @param array $data Validated input data containing sppg, partners, admin_sppg, and optional ahli_gizi/admin_logistik.
     * @return SPPG
     */
    public function register(array $data): SPPG
    {
        return DB::transaction(function () use ($data) {
            // 1. Create SPPG (status: inactive)
            $sppgData = $data['sppg'];
            $sppg = SPPG::create([
                'name' => $sppgData['name'],
                'address' => $sppgData['address'],
                'district' => $sppgData['district'],
                'city' => $sppgData['city'],
                'province' => $sppgData['province'],
                'latitude' => $sppgData['latitude'],
                'longitude' => $sppgData['longitude'],
                'capacity' => $sppgData['capacity'],
                'status' => 'inactive',
            ]);

            // Seed default roles and permissions for this SPPG
            $seeder = new DefaultRolePermissionSeeder();
            $seeder->seedForSppg($sppg->id);

            // 2. Create Admin SPPG user
            $adminData = $data['admin_sppg'];
            $adminUser = User::create([
                'name' => $adminData['name'],
                'email' => $adminData['email'],
                'password' => $adminData['password'], // hashed by User model cast
                'role_type' => 'sppg_user',
                'is_active' => true,
                'sppg_id' => $sppg->id,
            ]);

            // 3. Update SPPG pemilik_id to Admin SPPG user
            $sppg->pemilik_id = $adminUser->id;
            $sppg->save();

            // Assign default role "Admin SPPG" and create employee record
            $adminRole = Role::where('sppg_id', $sppg->id)->where('slug', 'sppg_admin')->first();
            Employee::create([
                'sppg_id' => $sppg->id,
                'user_id' => $adminUser->id,
                'role_id' => $adminRole?->id,
                'name'    => $adminUser->name,
                'position' => 'owner',
                'joined_at' => now(),
            ]);

            $loginLink = url('/login');

            // Queue email notification for Admin SPPG
            Mail::to($adminUser->email)->queue(
                new AccountCreatedMail(
                    name: $adminUser->name,
                    sppgName: $sppg->name,
                    email: $adminUser->email,
                    password: $adminData['password'],
                    loginLink: $loginLink
                )
            );

            // 4. Create optional Nutritionist user
            if (!empty($data['nutritionist']) && !empty($data['nutritionist']['email'])) {
                $giziData = $data['nutritionist'];
                $giziUser = User::create([
                    'name'      => $giziData['name'],
                    'email'     => $giziData['email'],
                    'password'  => $giziData['password'] ?? $adminData['password'],
                    'role_type' => 'sppg_user',
                    'is_active' => true,
                    'sppg_id'   => $sppg->id,
                ]);

                $giziRole = Role::where('sppg_id', $sppg->id)->where('slug', 'nutritionist')->first();
                Employee::create([
                    'sppg_id'   => $sppg->id,
                    'user_id'   => $giziUser->id,
                    'role_id'   => $giziRole?->id,
                    'name'      => $giziUser->name,
                    'position'  => 'nutritionist',
                    'joined_at' => now(),
                ]);

                Mail::to($giziUser->email)->queue(
                    new AccountCreatedMail(
                        name: $giziUser->name,
                        sppgName: $sppg->name,
                        email: $giziUser->email,
                        password: $giziData['password'] ?? $adminData['password'],
                        loginLink: $loginLink
                    )
                );
            }

            // 5. Create optional Logistics Admin user
            if (!empty($data['logistics_admin']) && !empty($data['logistics_admin']['email'])) {
                $logistikData = $data['logistics_admin'];
                $logistikUser = User::create([
                    'name'      => $logistikData['name'],
                    'email'     => $logistikData['email'],
                    'password'  => $logistikData['password'] ?? $adminData['password'],
                    'role_type' => 'sppg_user',
                    'is_active' => true,
                    'sppg_id'   => $sppg->id,
                ]);

                $logistikRole = Role::where('sppg_id', $sppg->id)->where('slug', 'logistics_admin')->first();
                Employee::create([
                    'sppg_id'   => $sppg->id,
                    'user_id'   => $logistikUser->id,
                    'role_id'   => $logistikRole?->id,
                    'name'      => $logistikUser->name,
                    'position'  => 'logistics_admin',
                    'joined_at' => now(),
                ]);

                Mail::to($logistikUser->email)->queue(
                    new AccountCreatedMail(
                        name:      $logistikUser->name,
                        sppgName:  $sppg->name,
                        email:     $logistikUser->email,
                        password:  $logistikData['password'] ?? $adminData['password'],
                        loginLink: $loginLink
                    )
                );
            }

            // 6. Insert/Update data mitra in partners table
            foreach ($data['partners'] as $partnerItem) {
                if (!empty($partnerItem['id'])) {
                    $partner = Partner::findOrFail($partnerItem['id']);
                    $partner->sppg_id = $sppg->id;
                    $partner->save();
                } else {
                    Partner::create([
                        'school_name' => $partnerItem['school_name'],
                        'npsn' => $partnerItem['npsn'] ?? null,
                        'school_type' => $partnerItem['school_type'],
                        'ownership_status' => $partnerItem['ownership_status'],
                        'address' => $partnerItem['address'] ?? null,
                        'district' => $partnerItem['district'] ?? null,
                        'city' => $partnerItem['city'] ?? null,
                        'latitude' => $partnerItem['latitude'],
                        'longitude' => $partnerItem['longitude'],
                        'portion_count' => $partnerItem['portion_count'] ?? 0,
                        'sppg_id' => $sppg->id,
                    ]);
                }
            }
            return $sppg->load('owner');
        });
    }
}