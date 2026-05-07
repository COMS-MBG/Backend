<?php

namespace Database\Seeders;

use App\Services\Employee\RBACService;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function __construct(private readonly RBACService $rbacService) {}

    public function run(): void
    {
        $this->rbacService->seedRolesAndPermissions();
        $this->command->info('Roles & permissions berhasil di-seed.');
    }
}