<?php

namespace Database\Seeders;

use App\Models\SPPG;
use Illuminate\Database\Seeder;

class SPPGSeeder extends Seeder
{
    public function run(): void
    {
        $sppgs = [
            [
                'name'      => 'SPPG Bandung Utara',
                'address'   => 'Jl. Sukajadi No. 10, Bandung',
                'latitude'  => -6.8798,
                'longitude' => 107.5908,
                'capacity'  => 10,
                'status'    => 'active',
                'district'  => 'Sukajadi',
                'city'      => 'Bandung',
                'province'  => 'Jawa Barat',
            ],
            [
                'name'      => 'SPPG Bandung Selatan',
                'address'   => 'Jl. Buah Batu No. 45, Bandung',
                'latitude'  => -6.9500,
                'longitude' => 107.6200,
                'capacity'  => 8,
                'status'    => 'active',
                'district'  => 'Buah Batu',
                'city'      => 'Bandung',
                'province'  => 'Jawa Barat',
            ],
            [
                'name'      => 'SPPG Bandung Timur',
                'address'   => 'Jl. Soekarno Hatta No. 200, Bandung',
                'latitude'  => -6.9350,
                'longitude' => 107.6800,
                'capacity'  => 12,
                'status'    => 'active',
                'district'  => 'Gedebage',
                'city'      => 'Bandung',
                'province'  => 'Jawa Barat',
            ],
        ];

        foreach ($sppgs as $data) {
            SPPG::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        $this->command->info('SPPG seed selesai: ' . count($sppgs) . ' SPPG.');
    }
}