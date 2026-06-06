<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PublicUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $publicUsers = [
            [
                'name'           => 'Rizky Ramadhan',
                'email'          => 'rizky@gmail.test',
                'otp_code'       => '123456',
                'otp_expires_at' => now()->addMinutes(10),
                'is_verified'    => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'name'           => 'Dewi Lestari',
                'email'          => 'dewi@outlook.test',
                'otp_code'       => '654321',
                'otp_expires_at' => now()->subMinutes(5), // expired OTP
                'is_verified'    => false,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'name'           => 'Fikri Alamsyah',
                'email'          => 'fikri@yahoo.test',
                'otp_code'       => null,
                'otp_expires_at' => null,
                'is_verified'    => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ];

        foreach ($publicUsers as $user) {
            DB::table('public_users')->updateOrInsert(
                ['email' => $user['email']],
                $user
            );
        }

        $this->command->info('PublicUsers seed selesai.');
    }
}
