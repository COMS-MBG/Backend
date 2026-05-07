<?php

namespace Database\Seeders;

use App\Models\SPPG;
use App\Models\User;
use Illuminate\Database\Seeder;

class SPPGSeeder extends Seeder
{
    public function run(): void
    {
        $pemilik = User::role('pemilik')->first();

        $sppgs = [
            [
                'nama'      => 'SPPG Bandung Utara',
                'alamat'    => 'Jl. Sukajadi No. 10, Bandung',
                'latitude'  => -6.8798,
                'longitude' => 107.5908,
                'kapasitas' => 10,
                'status'    => 'aktif',
                'kecamatan' => 'Sukajadi',
                'kota'      => 'Bandung',
                'provinsi'  => 'Jawa Barat',
            ],
            [
                'nama'      => 'SPPG Bandung Selatan',
                'alamat'    => 'Jl. Buah Batu No. 45, Bandung',
                'latitude'  => -6.9500,
                'longitude' => 107.6200,
                'kapasitas' => 8,
                'status'    => 'aktif',
                'kecamatan' => 'Buah Batu',
                'kota'      => 'Bandung',
                'provinsi'  => 'Jawa Barat',
            ],
            [
                'nama'      => 'SPPG Bandung Timur',
                'alamat'    => 'Jl. Soekarno Hatta No. 200, Bandung',
                'latitude'  => -6.9350,
                'longitude' => 107.6800,
                'kapasitas' => 12,
                'status'    => 'aktif',
                'kecamatan' => 'Gedebage',
                'kota'      => 'Bandung',
                'provinsi'  => 'Jawa Barat',
            ],
        ];

        foreach ($sppgs as $data) {
            SPPG::firstOrCreate(
                ['nama' => $data['nama']],
                array_merge($data, ['pemilik_id' => $pemilik?->id])
            );
        }

        $this->command->info('SPPG seed selesai: ' . count($sppgs) . ' SPPG.');
    }
}