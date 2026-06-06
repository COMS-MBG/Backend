<?php

namespace Database\Seeders;

use App\Models\SPPGSubmission;
use Illuminate\Database\Seeder;

class SPPGSubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tabel ini hanya memiliki id & timestamps saat ini
        for ($i = 1; $i <= 3; $i++) {
            SPPGSubmission::create([]);
        }

        $this->command->info('SPPGSubmission seeder dummy selesai.');
    }
}
