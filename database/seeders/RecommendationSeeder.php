<?php

namespace Database\Seeders;

use App\Models\Recommendation;
use Illuminate\Database\Seeder;

class RecommendationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tabel ini hanya memiliki id & timestamps saat ini
        for ($i = 1; $i <= 3; $i++) {
            Recommendation::create([]);
        }

        $this->command->info('Recommendation seeder dummy selesai.');
    }
}
