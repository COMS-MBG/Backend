<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $partners = [
            // ── Kota Bandung ──────────────────────────────────────
            [
                'school_name'      => 'SMA Negeri 1 Bandung',
                'npsn'             => '20219157',
                'school_type'      => 'SMA',
                'ownership_status' => 'public',
                'address'          => 'Jl. Ir. H. Juanda No.93, Tamansari',
                'latitude'         => -6.897042,
                'longitude'        => 107.608779,
                'district'         => 'Bandung Wetan',
                'city'             => 'Kota Bandung',
                'portion_count'    => 850,
            ],
            [
                'school_name'      => 'SMK Negeri 4 Bandung',
                'npsn'             => '20219162',
                'school_type'      => 'SMK',
                'ownership_status' => 'public',
                'address'          => 'Jl. Kliningan No.6, Turangga',
                'latitude'         => -6.937664,
                'longitude'        => 107.625624,
                'district'         => 'Lengkong',
                'city'             => 'Kota Bandung',
                'portion_count'    => 1200,
            ],
            [
                'school_name'      => 'SMA Pasundan 1 Bandung',
                'npsn'             => '20219170',
                'school_type'      => 'SMA',
                'ownership_status' => 'private',
                'address'          => 'Jl. Balonggede No.28, Regol',
                'latitude'         => -6.924294,
                'longitude'        => 107.604533,
                'district'         => 'Regol',
                'city'             => 'Kota Bandung',
                'portion_count'    => 420,
            ],
            [
                'school_name'      => 'SMA Negeri 3 Bandung',
                'npsn'             => '20219159',
                'school_type'      => 'SMA',
                'ownership_status' => 'public',
                'address'          => 'Jl. Belitung No.8, Merdeka',
                'latitude'         => -6.909405,
                'longitude'        => 107.611145,
                'district'         => 'Sumur Bandung',
                'city'             => 'Kota Bandung',
                'portion_count'    => 780,
            ],
            [
                'school_name'      => 'SMK Prakarya Internasional',
                'npsn'             => '20219181',
                'school_type'      => 'SMK',
                'ownership_status' => 'private',
                'address'          => 'Jl. Inhoftank No.146-148',
                'latitude'         => -6.945532,
                'longitude'        => 107.598501,
                'district'         => 'Babakan Ciparay',
                'city'             => 'Kota Bandung',
                'portion_count'    => 600,
            ],
            [
                'school_name'      => 'SMA Negeri 5 Bandung',
                'npsn'             => '20219161',
                'school_type'      => 'SMA',
                'ownership_status' => 'public',
                'address'          => 'Jl. Belitung No.8',
                'latitude'         => -6.909301,
                'longitude'        => 107.611832,
                'district'         => 'Sumur Bandung',
                'city'             => 'Kota Bandung',
                'portion_count'    => 720,
            ],
            [
                'school_name'      => 'SMK Negeri 7 Bandung',
                'npsn'             => '20219174',
                'school_type'      => 'SMK',
                'ownership_status' => 'public',
                'address'          => 'Jl. Soekarno-Hatta No.596',
                'latitude'         => -6.941916,
                'longitude'        => 107.632299,
                'district'         => 'Batununggal',
                'city'             => 'Kota Bandung',
                'portion_count'    => 1050,
            ],
            [
                'school_name'      => 'SMA Negeri 8 Bandung',
                'npsn'             => '20219175',
                'school_type'      => 'SMA',
                'ownership_status' => 'public',
                'address'          => 'Jl. Solontongan No.3, Buah Batu',
                'latitude'         => -6.939886,
                'longitude'        => 107.625078,
                'district'         => 'Buah Batu',
                'city'             => 'Kota Bandung',
                'portion_count'    => 680,
            ],

            // ── Kota Cimahi ───────────────────────────────────────
            [
                'school_name'      => 'SMK Negeri 1 Cimahi',
                'npsn'             => '20254012',
                'school_type'      => 'SMK',
                'ownership_status' => 'public',
                'address'          => 'Jl. Mahar Martanegara No.48',
                'latitude'         => -6.883733,
                'longitude'        => 107.534898,
                'district'         => 'Cimahi Selatan',
                'city'             => 'Kota Cimahi',
                'portion_count'    => 950,
            ],

            // ── Kabupaten Bandung Barat ───────────────────────────
            [
                'school_name'      => 'MA Al-Inayah',
                'npsn'             => '20281003',
                'school_type'      => 'MA',
                'ownership_status' => 'private',
                'address'          => 'Jl. Raya Padalarang No.12',
                'latitude'         => -6.840788,
                'longitude'        => 107.472146,
                'district'         => 'Padalarang',
                'city'             => 'Kabupaten Bandung Barat',
                'portion_count'    => 310,
            ],

            // ── Kabupaten Sumedang ────────────────────────────────
            [
                'school_name'      => 'MA Negeri 1 Sumedang',
                'npsn'             => '20281100',
                'school_type'      => 'MA',
                'ownership_status' => 'public',
                'address'          => 'Jl. Prabu Geusan Ulun No.36',
                'latitude'         => -6.853241,
                'longitude'        => 107.925574,
                'district'         => 'Sumedang Utara',
                'city'             => 'Kabupaten Sumedang',
                'portion_count'    => 550,
            ],

            // ── Kabupaten Bandung ─────────────────────────────────
            [
                'school_name'      => 'SMK Telkom Bandung',
                'npsn'             => '69942365',
                'school_type'      => 'SMK',
                'ownership_status' => 'private',
                'address'          => 'Jl. Dayeuhkolot No.116',
                'latitude'         => -6.974404,
                'longitude'        => 107.630040,
                'district'         => 'Dayeuhkolot',
                'city'             => 'Kabupaten Bandung',
                'portion_count'    => 480,
            ],
        ];

        foreach ($partners as $data) {
            Partner::firstOrCreate(
                ['npsn' => $data['npsn']],
                $data,
            );
        }

        $this->command->info('Partner seeder complete: ' . count($partners) . ' school partners seeded.');
    }
}
