<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\SPPG;
use App\Models\SPPGSchool;
use Illuminate\Database\Seeder;

class SPPGSchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sppgUtara   = SPPG::where('nama', 'SPPG Bandung Utara')->first();
        $sppgSelatan = SPPG::where('nama', 'SPPG Bandung Selatan')->first();

        // 1. Ambil semua sekolah
        $schools = School::all();

        foreach ($schools as $school) {
            // Tentukan sppg_id secara konsisten dengan data sekolah
            $sppgId = $school->sppg_id;
            
            if (!$sppgId) {
                // Jika tidak ada sppg_id (sekolah belum diasosiasikan),
                // kita biarkan beberapa kosong, atau kita assign beberapa secara acak untuk testing
                continue;
            }

            SPPGSchool::firstOrCreate(
                [
                    'sppg_id'   => $sppgId,
                    'school_id' => $school->id,
                ],
                [
                    'tanggal_bergabung' => now()->subMonths(rand(1, 12)),
                    'status'            => 'aktif',
                    'catatan'           => 'Asosiasi otomatis saat seeding data demo.',
                ]
            );
        }

        $this->command->info('SPPGSchool (Pivot) seed selesai.');
    }
}
