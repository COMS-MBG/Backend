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
        $sppgTimur   = SPPG::where('name', 'SPPG Bandung Timur')->first();

        $schools = [
            // Mitra SPPG Utara
            ['npsn' => '20219157', 'name' => 'SMA Negeri 1 Bandung',    'address' => 'Jl. Ir. H. Juanda No.93',    'latitude' => -6.8862, 'longitude' => 107.6147, 'student_count' => 1200, 'school_level' => 'SMA', 'city' => 'Bandung', 'sppg_id' => $sppgUtara?->id],
            ['npsn' => '20219162', 'name' => 'SMA Negeri 2 Bandung',    'address' => 'Jl. Cihampelas No.173',       'latitude' => -6.8920, 'longitude' => 107.6050, 'student_count' => 980,  'school_level' => 'SMA', 'city' => 'Bandung', 'sppg_id' => $sppgUtara?->id],
            ['npsn' => '20219170', 'name' => 'SMP Negeri 5 Bandung',    'address' => 'Jl. Sumatera No.40',          'latitude' => -6.9050, 'longitude' => 107.6120, 'student_count' => 750,  'school_level' => 'SMP', 'city' => 'Bandung', 'sppg_id' => $sppgUtara?->id],
            ['npsn' => '20219159', 'name' => 'SD Negeri 024 Coblong',   'address' => 'Jl. Puter No.3',              'latitude' => -6.8835, 'longitude' => 107.6155, 'student_count' => 450,  'school_level' => 'SD',  'city' => 'Bandung', 'sppg_id' => $sppgUtara?->id],
            ['npsn' => '20219181', 'name' => 'SD Negeri 032 Tilil',     'address' => 'Jl. Sadang Serang No.25',     'latitude' => -6.8905, 'longitude' => 107.6185, 'student_count' => 500,  'school_level' => 'SD',  'city' => 'Bandung', 'sppg_id' => $sppgUtara?->id],
            
            // Mitra SPPG Selatan
            ['npsn' => '20219161', 'name' => 'SMA Negeri 15 Bandung',   'address' => 'Jl. Sumbawa No.9',            'latitude' => -6.9420, 'longitude' => 107.6130, 'student_count' => 870,  'school_level' => 'SMA', 'city' => 'Bandung', 'sppg_id' => $sppgSelatan?->id],
            ['npsn' => '20219174', 'name' => 'SMK Negeri 4 Bandung',    'address' => 'Jl. Kliningan No.6',          'latitude' => -6.9580, 'longitude' => 107.6230, 'student_count' => 1100, 'school_level' => 'SMK', 'city' => 'Bandung', 'sppg_id' => $sppgSelatan?->id],
            ['npsn' => '20219175', 'name' => 'SMA Negeri 11 Bandung',   'address' => 'Jl. Kembar Baru No.4',        'latitude' => -6.9380, 'longitude' => 107.6275, 'student_count' => 920,  'school_level' => 'SMA', 'city' => 'Bandung', 'sppg_id' => $sppgSelatan?->id],
            ['npsn' => '20254012', 'name' => 'SMP Negeri 10 Bandung',   'address' => 'Jl. Situ Aksan No.8',         'latitude' => -6.9295, 'longitude' => 107.5995, 'student_count' => 640,  'school_level' => 'SMP', 'city' => 'Bandung', 'sppg_id' => $sppgSelatan?->id],
 
            // Mitra SPPG Timur
            ['npsn' => '20281003', 'name' => 'SMA Negeri 24 Bandung',   'address' => 'Jl. A.H. Nasution No.27',     'latitude' => -6.9077, 'longitude' => 107.6830, 'student_count' => 1050, 'school_level' => 'SMA', 'city' => 'Bandung', 'sppg_id' => $sppgTimur?->id],
            ['npsn' => '20281100', 'name' => 'SMP Negeri 1 Bandung',    'address' => 'Jl. Jend. A. Yani No.269',    'latitude' => -6.9082, 'longitude' => 107.6095, 'student_count' => 800,  'school_level' => 'SMP', 'city' => 'Bandung', 'sppg_id' => $sppgTimur?->id],
            ['npsn' => '69942365', 'name' => 'SD Negeri 113 Hanafi',    'address' => 'Jl. Cibiru Raya No.42',       'latitude' => -6.9160, 'longitude' => 107.6080, 'student_count' => 480,  'school_level' => 'SD',  'city' => 'Bandung', 'sppg_id' => $sppgTimur?->id],
 
            // Belum punya SPPG (Mitigasi)
            ['npsn' => '20206001', 'name' => 'SMA Negeri 20 Bandung',   'address' => 'Jl. Citarum No.23',           'latitude' => -6.9200, 'longitude' => 107.6350, 'student_count' => 650,  'school_level' => 'SMA', 'city' => 'Bandung', 'sppg_id' => null],
            ['npsn' => '20206002', 'name' => 'SMP Negeri 40 Bandung',   'address' => 'Jl. Wastukancana No.2',       'latitude' => -6.9100, 'longitude' => 107.6180, 'student_count' => 520,  'school_level' => 'SMP', 'city' => 'Bandung', 'sppg_id' => null],
            ['npsn' => '20206003', 'name' => 'SMA Negeri 3 Bandung',    'address' => 'Jl. Belitung No.8',           'latitude' => -6.9025, 'longitude' => 107.6119, 'student_count' => 1150, 'school_level' => 'SMA', 'city' => 'Bandung', 'sppg_id' => null],
            ['npsn' => '20206004', 'name' => 'SMA Negeri 8 Bandung',    'address' => 'Jl. Solontongan No.3',        'latitude' => -6.9248, 'longitude' => 107.6255, 'student_count' => 950,  'school_level' => 'SMA', 'city' => 'Bandung', 'sppg_id' => null],
            ['npsn' => '20206005', 'name' => 'SMA Negeri 9 Bandung',    'address' => 'Jl. Suparmin No.1A',          'latitude' => -6.9002, 'longitude' => 107.5785, 'student_count' => 880,  'school_level' => 'SMA', 'city' => 'Bandung', 'sppg_id' => null],
            ['npsn' => '20206006', 'name' => 'SMP Negeri 2 Bandung',    'address' => 'Jl. Sumatera No.42',          'latitude' => -6.9080, 'longitude' => 107.6150, 'student_count' => 700,  'school_level' => 'SMP', 'city' => 'Bandung', 'sppg_id' => null],
            ['npsn' => '20206007', 'name' => 'SD Negeri 056 Garuda',    'address' => 'Jl. Pajajaran No.84',         'latitude' => -6.9055, 'longitude' => 107.5790, 'student_count' => 380,  'school_level' => 'SD',  'city' => 'Bandung', 'sppg_id' => null],
            ['npsn' => '20206008', 'name' => 'SD Negeri 001 Merdeka',   'address' => 'Jl. Merdeka No.9',            'latitude' => -6.9135, 'longitude' => 107.6105, 'student_count' => 620,  'school_level' => 'SD',  'city' => 'Bandung', 'sppg_id' => null],
        ];

        foreach ($schools as $data) {
            School::updateOrCreate(['name' => $data['name']], array_merge($data, [
                'province' => 'Jawa Barat',
                'status'   => 'active',
            ]));
        }

        $this->command->info('School seed done: ' . count($schools) . ' schools.');
    }
}