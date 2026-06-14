<?php

echo "=== Seeding Schools for SPPG 11 (Lengkong) ===" . PHP_EOL;

$schoolsData = [
    [
        'npsn' => '20400401',
        'name' => 'SDN Lengkong 01',
        'address' => 'Jl. Lengkong Besar No. 1',
        'latitude' => -6.9350,
        'longitude' => 107.6350,
        'student_count' => 130,
        'school_level' => 'SD',
        'district' => 'Lengkong',
        'city' => 'Bandung',
        'province' => 'Jawa Barat',
        'phone' => '022-1234567',
        'principal' => 'Dr. Lengkong 1',
        'sppg_id' => 11,
        'status' => 'active',
    ],
    [
        'npsn' => '20400402',
        'name' => 'SMPN 14 Lengkong',
        'address' => 'Jl. Lengkong Kecil No. 14',
        'latitude' => -6.9380,
        'longitude' => 107.6380,
        'student_count' => 200,
        'school_level' => 'SMP',
        'district' => 'Lengkong',
        'city' => 'Bandung',
        'province' => 'Jawa Barat',
        'phone' => '022-1234568',
        'principal' => 'Dr. Lengkong 2',
        'sppg_id' => 11,
        'status' => 'active',
    ],
    [
        'npsn' => '20400403',
        'name' => 'SMAN 5 Lengkong',
        'address' => 'Jl. Belitung No. 5, Lengkong',
        'latitude' => -6.9320,
        'longitude' => 107.6320,
        'student_count' => 290,
        'school_level' => 'SMA',
        'district' => 'Lengkong',
        'city' => 'Bandung',
        'province' => 'Jawa Barat',
        'phone' => '022-1234569',
        'principal' => 'Dr. Lengkong 3',
        'sppg_id' => 11,
        'status' => 'active',
    ],
    [
        'npsn' => '20400404',
        'name' => 'SDN Cijagra 02 Lengkong',
        'address' => 'Jl. Cijagra No. 2, Lengkong',
        'latitude' => -6.9360,
        'longitude' => 107.6360,
        'student_count' => 110,
        'school_level' => 'SD',
        'district' => 'Lengkong',
        'city' => 'Bandung',
        'province' => 'Jawa Barat',
        'phone' => '022-1234570',
        'principal' => 'Dr. Lengkong 4',
        'sppg_id' => 11,
        'status' => 'active',
    ],
];

foreach ($schoolsData as $s) {
    $exists = DB::table('schools')->where('npsn', $s['npsn'])->first();
    if (!$exists) {
        $id = DB::table('schools')->insertGetId(array_merge($s, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));
        echo "  + Created School: {$s['name']} (ID: {$id})" . PHP_EOL;
    } else {
        echo "  ~ School already exists: {$s['name']} (ID: {$exists->id})" . PHP_EOL;
    }
}
