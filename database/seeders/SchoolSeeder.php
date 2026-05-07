<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\SPPG;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $sppgUtara   = SPPG::where('nama', 'SPPG Bandung Utara')->first();
        $sppgSelatan = SPPG::where('nama', 'SPPG Bandung Selatan')->first();

        $schools = [
            // Mitra SPPG Utara
            ['nama' => 'SMA Negeri 1 Bandung',    'alamat' => 'Jl. Ir. H. Juanda No.93',    'latitude' => -6.8862, 'longitude' => 107.6147, 'jumlah_siswa' => 1200, 'jenjang' => 'SMA', 'kota' => 'Bandung', 'sppg_id' => $sppgUtara?->id],
            ['nama' => 'SMA Negeri 2 Bandung',    'alamat' => 'Jl. Cihampelas No.173',       'latitude' => -6.8920, 'longitude' => 107.6050, 'jumlah_siswa' => 980,  'jenjang' => 'SMA', 'kota' => 'Bandung', 'sppg_id' => $sppgUtara?->id],
            ['nama' => 'SMP Negeri 5 Bandung',    'alamat' => 'Jl. Sumatera No.40',          'latitude' => -6.9050, 'longitude' => 107.6120, 'jumlah_siswa' => 750,  'jenjang' => 'SMP', 'kota' => 'Bandung', 'sppg_id' => $sppgUtara?->id],
            // Mitra SPPG Selatan
            ['nama' => 'SMA Negeri 15 Bandung',   'alamat' => 'Jl. Sumbawa No.9',            'latitude' => -6.9420, 'longitude' => 107.6130, 'jumlah_siswa' => 870,  'jenjang' => 'SMA', 'kota' => 'Bandung', 'sppg_id' => $sppgSelatan?->id],
            ['nama' => 'SMK Negeri 4 Bandung',    'alamat' => 'Jl. Kliningan No.6',          'latitude' => -6.9580, 'longitude' => 107.6230, 'jumlah_siswa' => 1100, 'jenjang' => 'SMK', 'kota' => 'Bandung', 'sppg_id' => $sppgSelatan?->id],
            // Belum punya SPPG
            ['nama' => 'SMA Negeri 20 Bandung',   'alamat' => 'Jl. Citarum No.23',           'latitude' => -6.9200, 'longitude' => 107.6350, 'jumlah_siswa' => 650,  'jenjang' => 'SMA', 'kota' => 'Bandung', 'sppg_id' => null],
            ['nama' => 'SMP Negeri 40 Bandung',   'alamat' => 'Jl. Wastukancana No.2',       'latitude' => -6.9100, 'longitude' => 107.6180, 'jumlah_siswa' => 520,  'jenjang' => 'SMP', 'kota' => 'Bandung', 'sppg_id' => null],
        ];

        foreach ($schools as $data) {
            School::firstOrCreate(['nama' => $data['nama']], array_merge($data, [
                'provinsi' => 'Jawa Barat',
                'status'   => 'aktif',
            ]));
        }

        $this->command->info('School seed selesai: ' . count($schools) . ' sekolah.');
    }
}