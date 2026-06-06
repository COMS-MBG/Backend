<?php

namespace Database\Seeders;

use App\Models\FinancialReport;
use Illuminate\Database\Seeder;

class FinancialReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Karena tabel ini kosong (hanya id & timestamps), 
        // kita buat beberapa baris dummy untuk testing relasi/CRUD.
        for ($i = 1; $i <= 3; $i++) {
            FinancialReport::create([]);
        }

        $this->command->info('FinancialReport seeder dummy selesai.');
    }
}
