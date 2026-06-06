<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Role;
use App\Models\SPPG;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed users, employees, dan assign roles.
     *
     * Arsitektur:
     *   - Super admin: user.role_type = 'super_admin', tidak perlu employee
     *   - SPPG user: user.role_type = 'sppg_user', role di employee.role_id
     */
    public function run(): void
    {
        // ── 1. Super Admin (tanpa employee, tanpa SPPG) ──────────────────────

        User::firstOrCreate(
            ['email' => 'superadmin@sppg.test'],
            [
                'name'              => 'Super Admin SPPG',
                'password'          => 'password123',
                'phone'             => '081200000000',
                'is_active'         => true,
                'role_type'         => 'super_admin',
                'sppg_id'           => null,
                'email_verified_at' => now(),
            ]
        );

        // ── 2. Ambil SPPG pertama (harus di-seed dulu via SPPGSeeder) ────────

        $sppg = SPPG::first();

        if (!$sppg) {
            $this->command->warn('Tidak ada SPPG. Skip user seeding untuk SPPG user.');
            return;
        }

        // ── 3. SPPG Users + Employees ────────────────────────────────────────

        $usersConfig = [
            [
                'name'     => 'Naufal Akbar',
                'email'    => 'naufal@sppg.test',
                'password' => '122004',
                'phone'    => '0813855550999',
                'role'     => 'sppg_admin',
                'position' => 'owner',
            ],
            [
                'name'     => 'Hilman',
                'email'    => 'hilman@sppg.test',
                'password' => '122003',
                'phone'    => '081384440888',
                'role'     => 'nutritionist',
                'position' => 'nutritionist',
            ],
            [
                'name'     => 'Adit',
                'email'    => 'adit@sppg.test',
                'password' => '122002',
                'phone'    => '081383330777',
                'role'     => 'logistics_admin',
                'position' => 'logistics_admin',
            ],
            [
                'name'     => 'Asep Kurir',
                'email'    => 'asep@sppg.test',
                'password' => '122001',
                'phone'    => '081381110555',
                'role'     => 'courier',
                'position' => 'courier',
            ],
            [
                'name'     => 'Bambang Kurir',
                'email'    => 'bambang@sppg.test',
                'password' => '122005',
                'phone'    => '081382220666',
                'role'     => 'courier',
                'position' => 'courier',
            ],
        ];

        foreach ($usersConfig as $config) {
            $role = Role::where('slug', $config['role'])->first();

            // Buat user
            $user = User::firstOrCreate(
                ['email' => $config['email']],
                [
                    'name'              => $config['name'],
                    'password'          => $config['password'],
                    'phone'             => $config['phone'],
                    'is_active'         => true,
                    'role_type'         => 'sppg_user',
                    'sppg_id'           => $sppg->id,
                    'email_verified_at' => now(),
                ]
            );

            // Buat employee dengan role
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'sppg_id'   => $sppg->id,
                    'user_id'   => $user->id,
                    'role_id'   => $role?->id,
                    'name'      => $config['name'],
                    'position'  => $config['position'],
                    'phone'     => $config['phone'],
                    'status'    => 'active',
                    'joined_at' => now(),
                ]
            );

            $this->command->info("User '{$config['name']}' + Employee (role: {$config['role']})");
        }

        // ── 4. Employee tanpa akun (untuk testing master data) ───────────────

        Employee::firstOrCreate(
            ['nik' => '3201010101010001'],
            [
                'sppg_id'   => $sppg->id,
                'user_id'   => null,
                'role_id'   => null,
                'name'      => 'Budi (Tanpa Akun)',
                'nik'       => '3201010101010001',
                'position'  => 'karyawan_operasional',
                'phone'     => '081300000001',
                'status'    => 'active',
                'joined_at' => now(),
            ]
        );

        $this->command->info("Employee tanpa akun: 'Budi (Tanpa Akun)'");
    }
}
