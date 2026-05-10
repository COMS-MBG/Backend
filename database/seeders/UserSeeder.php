<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usersData = [
            [
                'name' => 'Super Admin SPPG',
                'email' => 'superadmin@sppg.test',
                'role' => 'super_admin'
            ],
            [
                'name' => 'Pemilik SPPG',
                'email' => 'pemilik@sppg.test',
                'role' => 'pemilik'
            ],
            [
                'name' => 'Manajer SPPG',
                'email' => 'manajer@sppg.test',
                'role' => 'manajer'
            ],
            [
                'name' => 'Ahli Gizi SPPG',
                'email' => 'ahligizi@sppg.test',
                'role' => 'ahli_gizi'
            ],
            [
                'name' => 'Admin Logistik SPPG',
                'email' => 'logistik@sppg.test',
                'role' => 'admin_logistik'
            ],
            [
                'name' => 'Kurir SPPG',
                'email' => 'kurir@sppg.test',
                'role' => 'kurir'
            ],
            [
                'name' => 'Karyawan Operasional SPPG',
                'email' => 'operasional@sppg.test',
                'role' => 'karyawan_operasional'
            ],
        ];

        foreach ($usersData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password123'),
                    'phone' => '081234567890',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            // Assign the role if the user doesn't already have it
            if (!$user->hasRole($data['role'])) {
                $user->assignRole($data['role']);
            }
        }
    }
}
