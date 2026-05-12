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
                'nama_sekolah'   => 'SMA Negeri 1 Bandung',
                'npsn'           => '20219157',
                'bentuk'         => 'SMA',
                'status'         => 'Negeri',
                'alamat'         => 'Jl. Ir. H. Juanda No.93, Tamansari',
                'latitude'       => -6.897042,
                'longitude'      => 107.608779,
                'kecamatan'      => 'Bandung Wetan',
                'kabupaten_kota' => 'Kota Bandung',
                'jumlah_porsi'   => 850,
            ],
            [
                'nama_sekolah'   => 'SMK Negeri 4 Bandung',
                'npsn'           => '20219162',
                'bentuk'         => 'SMK',
                'status'         => 'Negeri',
                'alamat'         => 'Jl. Kliningan No.6, Turangga',
                'latitude'       => -6.937664,
                'longitude'      => 107.625624,
                'kecamatan'      => 'Lengkong',
                'kabupaten_kota' => 'Kota Bandung',
                'jumlah_porsi'   => 1200,
            ],
            [
                'nama_sekolah'   => 'SMA Pasundan 1 Bandung',
                'npsn'           => '20219170',
                'bentuk'         => 'SMA',
                'status'         => 'Swasta',
                'alamat'         => 'Jl. Balonggede No.28, Regol',
                'latitude'       => -6.924294,
                'longitude'      => 107.604533,
                'kecamatan'      => 'Regol',
                'kabupaten_kota' => 'Kota Bandung',
                'jumlah_porsi'   => 420,
            ],
            [
                'nama_sekolah'   => 'SMA Negeri 3 Bandung',
                'npsn'           => '20219159',
                'bentuk'         => 'SMA',
                'status'         => 'Negeri',
                'alamat'         => 'Jl. Belitung No.8, Merdeka',
                'latitude'       => -6.909405,
                'longitude'      => 107.611145,
                'kecamatan'      => 'Sumur Bandung',
                'kabupaten_kota' => 'Kota Bandung',
                'jumlah_porsi'   => 780,
            ],
            [
                'nama_sekolah'   => 'SMK Prakarya Internasional',
                'npsn'           => '20219181',
                'bentuk'         => 'SMK',
                'status'         => 'Swasta',
                'alamat'         => 'Jl. Inhoftank No.146-148',
                'latitude'       => -6.945532,
                'longitude'      => 107.598501,
                'kecamatan'      => 'Babakan Ciparay',
                'kabupaten_kota' => 'Kota Bandung',
                'jumlah_porsi'   => 600,
            ],
            [
                'nama_sekolah'   => 'SMA Negeri 5 Bandung',
                'npsn'           => '20219161',
                'bentuk'         => 'SMA',
                'status'         => 'Negeri',
                'alamat'         => 'Jl. Belitung No.8',
                'latitude'       => -6.909301,
                'longitude'      => 107.611832,
                'kecamatan'      => 'Sumur Bandung',
                'kabupaten_kota' => 'Kota Bandung',
                'jumlah_porsi'   => 720,
            ],
            [
                'nama_sekolah'   => 'SMK Negeri 7 Bandung',
                'npsn'           => '20219174',
                'bentuk'         => 'SMK',
                'status'         => 'Negeri',
                'alamat'         => 'Jl. Soekarno-Hatta No.596',
                'latitude'       => -6.941916,
                'longitude'      => 107.632299,
                'kecamatan'      => 'Batununggal',
                'kabupaten_kota' => 'Kota Bandung',
                'jumlah_porsi'   => 1050,
            ],
            [
                'nama_sekolah'   => 'SMA Negeri 8 Bandung',
                'npsn'           => '20219175',
                'bentuk'         => 'SMA',
                'status'         => 'Negeri',
                'alamat'         => 'Jl. Solontongan No.3, Buah Batu',
                'latitude'       => -6.939886,
                'longitude'      => 107.625078,
                'kecamatan'      => 'Buah Batu',
                'kabupaten_kota' => 'Kota Bandung',
                'jumlah_porsi'   => 680,
            ],

            // ── Kota Cimahi ───────────────────────────────────────
            [
                'nama_sekolah'   => 'SMK Negeri 1 Cimahi',
                'npsn'           => '20254012',
                'bentuk'         => 'SMK',
                'status'         => 'Negeri',
                'alamat'         => 'Jl. Mahar Martanegara No.48',
                'latitude'       => -6.883733,
                'longitude'      => 107.534898,
                'kecamatan'      => 'Cimahi Selatan',
                'kabupaten_kota' => 'Kota Cimahi',
                'jumlah_porsi'   => 950,
            ],

            // ── Kabupaten Bandung Barat ───────────────────────────
            [
                'nama_sekolah'   => 'MA Al-Inayah',
                'npsn'           => '20281003',
                'bentuk'         => 'MA',
                'status'         => 'Swasta',
                'alamat'         => 'Jl. Raya Padalarang No.12',
                'latitude'       => -6.840788,
                'longitude'      => 107.472146,
                'kecamatan'      => 'Padalarang',
                'kabupaten_kota' => 'Kabupaten Bandung Barat',
                'jumlah_porsi'   => 310,
            ],

            // ── Kabupaten Sumedang ────────────────────────────────
            [
                'nama_sekolah'   => 'MA Negeri 1 Sumedang',
                'npsn'           => '20281100',
                'bentuk'         => 'MA',
                'status'         => 'Negeri',
                'alamat'         => 'Jl. Prabu Geusan Ulun No.36',
                'latitude'       => -6.853241,
                'longitude'      => 107.925574,
                'kecamatan'      => 'Sumedang Utara',
                'kabupaten_kota' => 'Kabupaten Sumedang',
                'jumlah_porsi'   => 550,
            ],

            // ── Kabupaten Bandung ─────────────────────────────────
            [
                'nama_sekolah'   => 'SMK Telkom Bandung',
                'npsn'           => '69942365',
                'bentuk'         => 'SMK',
                'status'         => 'Swasta',
                'alamat'         => 'Jl. Dayeuhkolot No.116',
                'latitude'       => -6.974404,
                'longitude'      => 107.630040,
                'kecamatan'      => 'Dayeuhkolot',
                'kabupaten_kota' => 'Kabupaten Bandung',
                'jumlah_porsi'   => 480,
            ],
        ];

        foreach ($partners as $data) {
            Partner::firstOrCreate(
                ['npsn' => $data['npsn']],
                $data,
            );
        }

        $this->command->info('Partner seed selesai: ' . count($partners) . ' sekolah mitra.');
    }
}
