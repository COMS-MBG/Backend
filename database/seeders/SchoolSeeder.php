<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\SPPG;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $sppgUtara   = SPPG::where('name', 'SPPG Bandung Utara')->first();
        $sppgSelatan = SPPG::where('name', 'SPPG Bandung Selatan')->first();
<<<<<<< Updated upstream
        $sppgTimur   = SPPG::where('name', 'SPPG Bandung Timur')->first();

        $schools = [
            // Mitra SPPG Utara
            ['nama' => 'SMA Negeri 1 Bandung',    'alamat' => 'Jl. Ir. H. Juanda No.93',    'latitude' => -6.8862, 'longitude' => 107.6147, 'jumlah_siswa' => 1200, 'jenjang' => 'SMA', 'kota' => 'Bandung', 'sppg_id' => $sppgUtara?->id],
            ['nama' => 'SMA Negeri 2 Bandung',    'alamat' => 'Jl. Cihampelas No.173',       'latitude' => -6.8920, 'longitude' => 107.6050, 'jumlah_siswa' => 980,  'jenjang' => 'SMA', 'kota' => 'Bandung', 'sppg_id' => $sppgUtara?->id],
            ['nama' => 'SMP Negeri 5 Bandung',    'alamat' => 'Jl. Sumatera No.40',          'latitude' => -6.9050, 'longitude' => 107.6120, 'jumlah_siswa' => 750,  'jenjang' => 'SMP', 'kota' => 'Bandung', 'sppg_id' => $sppgUtara?->id],
            ['nama' => 'SD Negeri 024 Coblong',   'alamat' => 'Jl. Puter No.3',              'latitude' => -6.8835, 'longitude' => 107.6155, 'jumlah_siswa' => 450,  'jenjang' => 'SD',  'kota' => 'Bandung', 'sppg_id' => $sppgUtara?->id],
            ['nama' => 'SD Negeri 032 Tilil',     'alamat' => 'Jl. Sadang Serang No.25',     'latitude' => -6.8905, 'longitude' => 107.6185, 'jumlah_siswa' => 500,  'jenjang' => 'SD',  'kota' => 'Bandung', 'sppg_id' => $sppgUtara?->id],
            
            // Mitra SPPG Selatan
            ['nama' => 'SMA Negeri 15 Bandung',   'alamat' => 'Jl. Sumbawa No.9',            'latitude' => -6.9420, 'longitude' => 107.6130, 'jumlah_siswa' => 870,  'jenjang' => 'SMA', 'kota' => 'Bandung', 'sppg_id' => $sppgSelatan?->id],
            ['nama' => 'SMK Negeri 4 Bandung',    'alamat' => 'Jl. Kliningan No.6',          'latitude' => -6.9580, 'longitude' => 107.6230, 'jumlah_siswa' => 1100, 'jenjang' => 'SMK', 'kota' => 'Bandung', 'sppg_id' => $sppgSelatan?->id],
            ['nama' => 'SMA Negeri 11 Bandung',   'alamat' => 'Jl. Kembar Baru No.4',        'latitude' => -6.9380, 'longitude' => 107.6275, 'jumlah_siswa' => 920,  'jenjang' => 'SMA', 'kota' => 'Bandung', 'sppg_id' => $sppgSelatan?->id],
            ['nama' => 'SMP Negeri 10 Bandung',   'alamat' => 'Jl. Situ Aksan No.8',         'latitude' => -6.9295, 'longitude' => 107.5995, 'jumlah_siswa' => 640,  'jenjang' => 'SMP', 'kota' => 'Bandung', 'sppg_id' => $sppgSelatan?->id],

            // Mitra SPPG Timur
            ['nama' => 'SMA Negeri 24 Bandung',   'alamat' => 'Jl. A.H. Nasution No.27',     'latitude' => -6.9077, 'longitude' => 107.6830, 'jumlah_siswa' => 1050, 'jenjang' => 'SMA', 'kota' => 'Bandung', 'sppg_id' => $sppgTimur?->id],
            ['nama' => 'SMP Negeri 1 Bandung',    'alamat' => 'Jl. Jend. A. Yani No.269',    'latitude' => -6.9082, 'longitude' => 107.6095, 'jumlah_siswa' => 800,  'jenjang' => 'SMP', 'kota' => 'Bandung', 'sppg_id' => $sppgTimur?->id],
            ['nama' => 'SD Negeri 113 Hanafi',    'alamat' => 'Jl. Cibiru Raya No.42',       'latitude' => -6.9160, 'longitude' => 107.6080, 'jumlah_siswa' => 480,  'jenjang' => 'SD',  'kota' => 'Bandung', 'sppg_id' => $sppgTimur?->id],

            // Belum punya SPPG (Mitigasi)
            ['nama' => 'SMA Negeri 20 Bandung',   'alamat' => 'Jl. Citarum No.23',           'latitude' => -6.9200, 'longitude' => 107.6350, 'jumlah_siswa' => 650,  'jenjang' => 'SMA', 'kota' => 'Bandung', 'sppg_id' => null],
            ['nama' => 'SMP Negeri 40 Bandung',   'alamat' => 'Jl. Wastukancana No.2',       'latitude' => -6.9100, 'longitude' => 107.6180, 'jumlah_siswa' => 520,  'jenjang' => 'SMP', 'kota' => 'Bandung', 'sppg_id' => null],
            ['nama' => 'SMA Negeri 3 Bandung',    'alamat' => 'Jl. Belitung No.8',           'latitude' => -6.9025, 'longitude' => 107.6119, 'jumlah_siswa' => 1150, 'jenjang' => 'SMA', 'kota' => 'Bandung', 'sppg_id' => null],
            ['nama' => 'SMA Negeri 8 Bandung',    'alamat' => 'Jl. Solontongan No.3',        'latitude' => -6.9248, 'longitude' => 107.6255, 'jumlah_siswa' => 950,  'jenjang' => 'SMA', 'kota' => 'Bandung', 'sppg_id' => null],
            ['nama' => 'SMA Negeri 9 Bandung',    'alamat' => 'Jl. Suparmin No.1A',          'latitude' => -6.9002, 'longitude' => 107.5785, 'jumlah_siswa' => 880,  'jenjang' => 'SMA', 'kota' => 'Bandung', 'sppg_id' => null],
            ['nama' => 'SMP Negeri 2 Bandung',    'alamat' => 'Jl. Sumatera No.42',          'latitude' => -6.9080, 'longitude' => 107.6150, 'jumlah_siswa' => 700,  'jenjang' => 'SMP', 'kota' => 'Bandung', 'sppg_id' => null],
            ['nama' => 'SD Negeri 056 Garuda',    'alamat' => 'Jl. Pajajaran No.84',         'latitude' => -6.9055, 'longitude' => 107.5790, 'jumlah_siswa' => 380,  'jenjang' => 'SD',  'kota' => 'Bandung', 'sppg_id' => null],
            ['nama' => 'SD Negeri 001 Merdeka',   'alamat' => 'Jl. Merdeka No.9',            'latitude' => -6.9135, 'longitude' => 107.6105, 'jumlah_siswa' => 620,  'jenjang' => 'SD',  'kota' => 'Bandung', 'sppg_id' => null],
=======

        $schools = [
            // Partners of SPPG Utara
            ['name' => 'SMA Negeri 1 Bandung',  'address' => 'Jl. Ir. H. Juanda No.93',  'latitude' => -6.8862, 'longitude' => 107.6147, 'student_count' => 1200, 'school_level' => 'SMA', 'city' => 'Bandung', 'sppg_id' => $sppgUtara?->id],
            ['name' => 'SMA Negeri 2 Bandung',  'address' => 'Jl. Cihampelas No.173',     'latitude' => -6.8920, 'longitude' => 107.6050, 'student_count' => 980,  'school_level' => 'SMA', 'city' => 'Bandung', 'sppg_id' => $sppgUtara?->id],
            ['name' => 'SMP Negeri 5 Bandung',  'address' => 'Jl. Sumatera No.40',        'latitude' => -6.9050, 'longitude' => 107.6120, 'student_count' => 750,  'school_level' => 'SMP', 'city' => 'Bandung', 'sppg_id' => $sppgUtara?->id],
            // Partners of SPPG Selatan
            ['name' => 'SMA Negeri 15 Bandung', 'address' => 'Jl. Sumbawa No.9',          'latitude' => -6.9420, 'longitude' => 107.6130, 'student_count' => 870,  'school_level' => 'SMA', 'city' => 'Bandung', 'sppg_id' => $sppgSelatan?->id],
            ['name' => 'SMK Negeri 4 Bandung',  'address' => 'Jl. Kliningan No.6',        'latitude' => -6.9580, 'longitude' => 107.6230, 'student_count' => 1100, 'school_level' => 'SMK', 'city' => 'Bandung', 'sppg_id' => $sppgSelatan?->id],
            // No SPPG assigned yet
            ['name' => 'SMA Negeri 20 Bandung', 'address' => 'Jl. Citarum No.23',         'latitude' => -6.9200, 'longitude' => 107.6350, 'student_count' => 650,  'school_level' => 'SMA', 'city' => 'Bandung', 'sppg_id' => null],
            ['name' => 'SMP Negeri 40 Bandung', 'address' => 'Jl. Wastukancana No.2',     'latitude' => -6.9100, 'longitude' => 107.6180, 'student_count' => 520,  'school_level' => 'SMP', 'city' => 'Bandung', 'sppg_id' => null],
>>>>>>> Stashed changes
        ];

        foreach ($schools as $data) {
            School::firstOrCreate(['name' => $data['name']], array_merge($data, [
                'province' => 'West Java',
                'status'   => 'active',
            ]));
        }

        $this->command->info('School seed done: ' . count($schools) . ' schools.');
    }
}