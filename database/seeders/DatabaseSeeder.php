<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Urutan penting:
     * 1. Permissions dulu (dependency dari roles)
     * 2. Roles (sync permissions)
     * 3. SPPG (dependency dari users/employees)
     * 4. Users + Employees (butuh SPPG + roles)
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            SPPGSeeder::class,
            UserSeeder::class,
            PartnerSeeder::class,
        ]);
    }
}
