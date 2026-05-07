<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\Auth\RBACService;

class RBACSeeder extends Seeder
{
    public function run(): void
    {
        app(RBACService::class)->seedRolesAndPermissions();
    }
}