<?php

namespace Database\Seeders;

use App\Models\Rating;
use Illuminate\Database\Seeder;

class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tabel ini hanya memiliki id & timestamps saat ini
        for ($i = 1; $i <= 5; $i++) {
            Rating::create([]);
        }

        $this->command->info('Rating seeder dummy selesai.');
    }
}
