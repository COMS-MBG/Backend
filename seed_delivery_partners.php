<?php

use Carbon\Carbon;

// ===================================================================
// SEED SCRIPT: Partners, Delivery Schedules & Delivery Histories
// Untuk: SPPG 1, 2, 3, 4, 9, 11
// Note: delivery_schedules & delivery_histories use bigint auto-increment
//       partners uses UUID/ULID
// ===================================================================

// ─── Helpers ────────────────────────────────────────────────────────
function makePartnerId() {
    return \Illuminate\Support\Str::ulid()->toRfc4122();
}

// ─── STEP 1: Seed Partners (Mitra Sekolah) per SPPG ───────────────
echo "=== Seeding Partners ===" . PHP_EOL;

$partnerData = [
    // SPPG 2 (Bandung Selatan)
    ['sppg_id' => 2, 'school_name' => 'SDN 001 Merdeka', 'npsn' => '20400101', 'school_type' => 'SD', 'ownership_status' => 'public', 'address' => 'Jl. Merdeka No. 1, Bandung Selatan', 'district' => 'Bandung Selatan', 'city' => 'Bandung', 'latitude' => -6.9400, 'longitude' => 107.6100, 'portion_count' => 120],
    ['sppg_id' => 2, 'school_name' => 'SMPN 15 Bandung Selatan', 'npsn' => '20400102', 'school_type' => 'SMP', 'ownership_status' => 'public', 'address' => 'Jl. Sudirman No. 15, Bandung Selatan', 'district' => 'Bandung Selatan', 'city' => 'Bandung', 'latitude' => -6.9450, 'longitude' => 107.6150, 'portion_count' => 180],
    ['sppg_id' => 2, 'school_name' => 'SMKN 8 Bandung Selatan', 'npsn' => '20400103', 'school_type' => 'SMK', 'ownership_status' => 'public', 'address' => 'Jl. Tilil No. 32, Bandung', 'district' => 'Bandung Selatan', 'city' => 'Bandung', 'latitude' => -6.9500, 'longitude' => 107.6200, 'portion_count' => 200],

    // SPPG 3 (Bandung Timur)
    ['sppg_id' => 3, 'school_name' => 'SDN Cicadas 01', 'npsn' => '20400201', 'school_type' => 'SD', 'ownership_status' => 'public', 'address' => 'Jl. Cicadas No. 1, Bandung Timur', 'district' => 'Bandung Timur', 'city' => 'Bandung', 'latitude' => -6.9200, 'longitude' => 107.6700, 'portion_count' => 150],
    ['sppg_id' => 3, 'school_name' => 'SMPN 7 Bandung Timur', 'npsn' => '20400202', 'school_type' => 'SMP', 'ownership_status' => 'public', 'address' => 'Jl. Ahmad Yani No. 7, Bandung Timur', 'district' => 'Bandung Timur', 'city' => 'Bandung', 'latitude' => -6.9250, 'longitude' => 107.6750, 'portion_count' => 220],
    ['sppg_id' => 3, 'school_name' => 'SMAN 24 Bandung Timur', 'npsn' => '20400203', 'school_type' => 'SMA', 'ownership_status' => 'public', 'address' => 'Jl. Soekarno Hatta No. 24, Bandung Timur', 'district' => 'Bandung Timur', 'city' => 'Bandung', 'latitude' => -6.9300, 'longitude' => 107.6800, 'portion_count' => 300],
    ['sppg_id' => 3, 'school_name' => 'SDN Cijagra Timur 05', 'npsn' => '20400204', 'school_type' => 'SD', 'ownership_status' => 'public', 'address' => 'Jl. Hanafi No. 5, Bandung Timur', 'district' => 'Bandung Timur', 'city' => 'Bandung', 'latitude' => -6.9350, 'longitude' => 107.6850, 'portion_count' => 160],

    // SPPG 4 (Batununggal)
    ['sppg_id' => 4, 'school_name' => 'SDN Batununggal 01', 'npsn' => '20400301', 'school_type' => 'SD', 'ownership_status' => 'public', 'address' => 'Jl. Batununggal Raya No. 1', 'district' => 'Batununggal', 'city' => 'Bandung', 'latitude' => -6.9600, 'longitude' => 107.6400, 'portion_count' => 140],
    ['sppg_id' => 4, 'school_name' => 'SMPN 18 Batununggal', 'npsn' => '20400302', 'school_type' => 'SMP', 'ownership_status' => 'public', 'address' => 'Jl. Batununggal No. 20', 'district' => 'Batununggal', 'city' => 'Bandung', 'latitude' => -6.9650, 'longitude' => 107.6450, 'portion_count' => 250],
    ['sppg_id' => 4, 'school_name' => 'SMAN 12 Batununggal', 'npsn' => '20400303', 'school_type' => 'SMA', 'ownership_status' => 'public', 'address' => 'Jl. Kloneng No. 12, Batununggal', 'district' => 'Batununggal', 'city' => 'Bandung', 'latitude' => -6.9700, 'longitude' => 107.6500, 'portion_count' => 280],
    ['sppg_id' => 4, 'school_name' => 'SMKN 3 Batununggal', 'npsn' => '20400304', 'school_type' => 'SMK', 'ownership_status' => 'public', 'address' => 'Jl. LMU No. 3, Batununggal', 'district' => 'Batununggal', 'city' => 'Bandung', 'latitude' => -6.9680, 'longitude' => 107.6480, 'portion_count' => 260],

    // SPPG 11 (Lengkong)
    ['sppg_id' => 11, 'school_name' => 'SDN Lengkong 01', 'npsn' => '20400401', 'school_type' => 'SD', 'ownership_status' => 'public', 'address' => 'Jl. Lengkong Besar No. 1', 'district' => 'Lengkong', 'city' => 'Bandung', 'latitude' => -6.9350, 'longitude' => 107.6350, 'portion_count' => 130],
    ['sppg_id' => 11, 'school_name' => 'SMPN 14 Lengkong', 'npsn' => '20400402', 'school_type' => 'SMP', 'ownership_status' => 'public', 'address' => 'Jl. Lengkong Kecil No. 14', 'district' => 'Lengkong', 'city' => 'Bandung', 'latitude' => -6.9380, 'longitude' => 107.6380, 'portion_count' => 200],
    ['sppg_id' => 11, 'school_name' => 'SMAN 5 Lengkong', 'npsn' => '20400403', 'school_type' => 'SMA', 'ownership_status' => 'public', 'address' => 'Jl. Belitung No. 5, Lengkong', 'district' => 'Lengkong', 'city' => 'Bandung', 'latitude' => -6.9320, 'longitude' => 107.6320, 'portion_count' => 290],
    ['sppg_id' => 11, 'school_name' => 'SDN Cijagra 02 Lengkong', 'npsn' => '20400404', 'school_type' => 'SD', 'ownership_status' => 'public', 'address' => 'Jl. Cijagra No. 2, Lengkong', 'district' => 'Lengkong', 'city' => 'Bandung', 'latitude' => -6.9360, 'longitude' => 107.6360, 'portion_count' => 110],
];

$insertedPartnerCount = 0;
foreach ($partnerData as $p) {
    $exists = DB::table('partners')->where('npsn', $p['npsn'])->exists();
    if (!$exists) {
        DB::table('partners')->insert([
            'id'               => makePartnerId(),
            'sppg_id'          => $p['sppg_id'],
            'school_name'      => $p['school_name'],
            'npsn'             => $p['npsn'],
            'school_type'      => $p['school_type'],
            'ownership_status' => $p['ownership_status'],
            'address'          => $p['address'],
            'district'         => $p['district'],
            'city'             => $p['city'],
            'latitude'         => $p['latitude'],
            'longitude'        => $p['longitude'],
            'portion_count'    => $p['portion_count'],
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        $insertedPartnerCount++;
        echo "  + Partner: {$p['school_name']} (SPPG {$p['sppg_id']})" . PHP_EOL;
    } else {
        echo "  ~ Skip (exists): {$p['school_name']} NPSN:{$p['npsn']}" . PHP_EOL;
    }
}
echo "Partners inserted: {$insertedPartnerCount}" . PHP_EOL . PHP_EOL;

// ─── STEP 2: Seed Delivery Schedules + Histories ───────────────────
// delivery_schedules.id = bigint auto-increment (DO NOT pass 'id')
// delivery_histories.id = bigint auto-increment (DO NOT pass 'id')

echo "=== Seeding Delivery Schedules & Histories ===" . PHP_EOL;

$deliverySetups = [
    [
        'sppg_id'    => 1,
        'admin_user' => 2,
        'couriers'   => [
            ['emp_id' => 4, 'name' => 'Asep Kurir',    'vehicle' => 'motorcycle', 'plate' => 'D 1234 AB'],
            ['emp_id' => 5, 'name' => 'Bambang Kurir',  'vehicle' => 'car',        'plate' => 'D 5678 CD'],
            ['emp_id' => 7, 'name' => 'Agus Kurir',     'vehicle' => 'motorcycle', 'plate' => 'D 9012 EF'],
        ],
        'schools' => [
            ['id' => 1,  'name' => 'SMA Negeri 1 Bandung',  'address' => 'Jl. Raya Dago No. 1',      'lat' => -6.870, 'lng' => 107.613],
            ['id' => 2,  'name' => 'SMA Negeri 2 Bandung',  'address' => 'Jl. Cihampelas No. 2',     'lat' => -6.875, 'lng' => 107.611],
            ['id' => 3,  'name' => 'SMP Negeri 5 Bandung',  'address' => 'Jl. Sumatera No. 5',      'lat' => -6.880, 'lng' => 107.614],
            ['id' => 4,  'name' => 'SD Negeri 024 Coblong', 'address' => 'Jl. Coblong No. 24',       'lat' => -6.877, 'lng' => 107.616],
        ],
    ],
    [
        'sppg_id'    => 2,
        'admin_user' => 29,
        'couriers'   => [
            ['emp_id' => 31, 'name' => 'Asep Selatan',   'vehicle' => 'motorcycle', 'plate' => 'D 2345 GH'],
            ['emp_id' => 32, 'name' => 'Bambang Selatan', 'vehicle' => 'car',        'plate' => 'D 6789 IJ'],
        ],
        'schools' => [
            ['id' => 5,  'name' => 'SD Negeri 032 Tilil',    'address' => 'Jl. Tilil No. 32',       'lat' => -6.930, 'lng' => 107.615],
            ['id' => 6,  'name' => 'SMA Negeri 15 Bandung',  'address' => 'Jl. Mengger No. 15',     'lat' => -6.940, 'lng' => 107.617],
            ['id' => 7,  'name' => 'SMK Negeri 4 Bandung',   'address' => 'Jl. Kloneng No. 4',      'lat' => -6.938, 'lng' => 107.619],
            ['id' => 8,  'name' => 'SMA Negeri 11 Bandung',  'address' => 'Jl. Pelajar No. 11',     'lat' => -6.945, 'lng' => 107.620],
        ],
    ],
    [
        'sppg_id'    => 3,
        'admin_user' => 34,
        'couriers'   => [
            ['emp_id' => 36, 'name' => 'Asep Timur',    'vehicle' => 'motorcycle', 'plate' => 'D 3456 KL'],
            ['emp_id' => 37, 'name' => 'Bambang Timur', 'vehicle' => 'car',        'plate' => 'D 7890 MN'],
        ],
        'schools' => [
            ['id' => 9,  'name' => 'SMP Negeri 10 Bandung', 'address' => 'Jl. Pagergunung No. 10', 'lat' => -6.920, 'lng' => 107.680],
            ['id' => 10, 'name' => 'SMA Negeri 24 Bandung', 'address' => 'Jl. Rancabolang No. 24', 'lat' => -6.925, 'lng' => 107.685],
            ['id' => 11, 'name' => 'SMP Negeri 1 Bandung',  'address' => 'Jl. Ternate No. 1',      'lat' => -6.922, 'lng' => 107.682],
            ['id' => 12, 'name' => 'SD Negeri 113 Hanafi',  'address' => 'Jl. Hanafi No. 113',     'lat' => -6.918, 'lng' => 107.678],
        ],
    ],
    [
        'sppg_id'    => 9,
        'admin_user' => 18,
        'couriers'   => [
            ['emp_id' => 25, 'name' => 'Asep Kurir Coblong', 'vehicle' => 'motorcycle', 'plate' => 'D 4567 OP'],
        ],
        'schools' => [
            ['id' => 22, 'name' => 'SD Negeri Coblong 1',  'address' => 'Jl. Coblong No. 1',     'lat' => -6.885, 'lng' => 107.618],
            ['id' => 23, 'name' => 'SD Negeri Coblong 2',  'address' => 'Jl. Coblong No. 2',     'lat' => -6.887, 'lng' => 107.620],
            ['id' => 24, 'name' => 'SD Negeri Lebak Gede', 'address' => 'Jl. Lebak Gede No. 1',  'lat' => -6.882, 'lng' => 107.615],
            ['id' => 25, 'name' => 'SD Negeri Dago',       'address' => 'Jl. Dago No. 5',         'lat' => -6.879, 'lng' => 107.613],
        ],
    ],
];

$totalSchedules = 0;
$totalHistories = 0;

foreach ($deliverySetups as $setup) {
    $sppgId    = $setup['sppg_id'];
    $adminUser = $setup['admin_user'];
    $couriers  = $setup['couriers'];
    $schools   = $setup['schools'];

    echo PHP_EOL . "--- SPPG {$sppgId} ---" . PHP_EOL;

    // 13 hari lalu sampai kemarin
    $dates = [];
    for ($i = 13; $i >= 1; $i--) {
        $dates[] = Carbon::now()->subDays($i)->startOfDay();
    }

    $courierIdx = 0;
    $schoolIdx  = 0;

    foreach ($dates as $date) {
        $deliveriesPerDay = rand(1, min(count($schools), count($couriers) + 1));

        for ($d = 0; $d < $deliveriesPerDay; $d++) {
            $courier = $couriers[$courierIdx % count($couriers)];
            $school  = $schools[$schoolIdx % count($schools)];

            $scheduledAt = $date->copy()->addHours(6)->addMinutes(rand(0, 30));
            $departedAt  = $scheduledAt->copy()->addMinutes(rand(5, 20));
            $arrivedAt   = $departedAt->copy()->addMinutes(rand(15, 45));
            $confirmedAt = $arrivedAt->copy()->addMinutes(rand(5, 30));
            $distanceKm  = round(rand(15, 80) / 10, 1);

            // Insert delivery schedule — DO NOT pass 'id' (bigint auto-increment)
            $schedId = DB::table('delivery_schedules')->insertGetId([
                'courier_id'     => $courier['emp_id'],
                'school_id'      => $school['id'],
                'assigned_by'    => $adminUser,
                'submitted_by'   => $adminUser,
                'vehicle_type'   => $courier['vehicle'],
                'vehicle_plate'  => $courier['plate'],
                'status'         => 'confirmed',
                'scheduled_at'   => $scheduledAt,
                'departed_at'    => $departedAt,
                'arrived_at'     => $arrivedAt,
                'confirmed_by'   => $adminUser,
                'confirmed_at'   => $confirmedAt,
                'delivery_notes' => 'Pengiriman MBG ' . $date->format('d/m/Y'),
                'route_snapshot' => json_encode([
                    'origin'      => ['lat' => -6.900 + rand(-30, 30) / 1000, 'lng' => 107.620 + rand(-30, 30) / 1000],
                    'destination' => ['lat' => $school['lat'], 'lng' => $school['lng']],
                ]),
                'created_at'     => $scheduledAt,
                'updated_at'     => $confirmedAt,
            ]);

            // Insert delivery history — DO NOT pass 'id' (bigint auto-increment)
            DB::table('delivery_histories')->insert([
                'delivery_schedule_id' => $schedId,
                'courier_id'           => $courier['emp_id'],
                'school_id'            => $school['id'],
                'courier_name'         => $courier['name'],
                'school_name'          => $school['name'],
                'school_address'       => $school['address'],
                'vehicle_type'         => $courier['vehicle'],
                'vehicle_plate'        => $courier['plate'],
                'departed_at'          => $departedAt,
                'arrived_at'           => $arrivedAt,
                'distance_km'          => $distanceKm,
                'confirmed_by'         => $adminUser,
                'confirmed_at'         => $confirmedAt,
                'notes'                => 'Pengiriman MBG ke ' . $school['name'],
                'route_snapshot'       => json_encode([
                    'distance_km' => $distanceKm,
                    'path' => [
                        ['lat' => -6.900, 'lng' => 107.620],
                        ['lat' => $school['lat'], 'lng' => $school['lng']],
                    ],
                ]),
                'created_at'           => $arrivedAt,
                'updated_at'           => $confirmedAt,
            ]);

            $totalSchedules++;
            $totalHistories++;
            $courierIdx++;
            $schoolIdx++;
        }
    }

    echo "  OK: " . count($dates) . " hari, scheduled + history inserted untuk SPPG {$sppgId}" . PHP_EOL;
}

// ─── STEP 3: Jadwal upcoming (besok, status in_order) ─────────────
echo PHP_EOL . "=== Adding upcoming schedules (in_order) ===" . PHP_EOL;

$upcomingSetups = [
    ['sppg_id' => 1, 'admin_user' => 2,  'courier_emp' => 4,  'vehicle' => 'motorcycle', 'plate' => 'D 1234 AB', 'school_id' => 1,  'school_name' => 'SMA Negeri 1 Bandung'],
    ['sppg_id' => 1, 'admin_user' => 2,  'courier_emp' => 5,  'vehicle' => 'car',        'plate' => 'D 5678 CD', 'school_id' => 2,  'school_name' => 'SMA Negeri 2 Bandung'],
    ['sppg_id' => 2, 'admin_user' => 29, 'courier_emp' => 31, 'vehicle' => 'motorcycle', 'plate' => 'D 2345 GH', 'school_id' => 5,  'school_name' => 'SD Negeri 032 Tilil'],
    ['sppg_id' => 3, 'admin_user' => 34, 'courier_emp' => 36, 'vehicle' => 'motorcycle', 'plate' => 'D 3456 KL', 'school_id' => 9,  'school_name' => 'SMP Negeri 10 Bandung'],
    ['sppg_id' => 9, 'admin_user' => 18, 'courier_emp' => 25, 'vehicle' => 'motorcycle', 'plate' => 'D 4567 OP', 'school_id' => 22, 'school_name' => 'SD Negeri Coblong 1'],
];

foreach ($upcomingSetups as $up) {
    $schedAt = Carbon::tomorrow()->setTime(7, 0);

    // DO NOT pass 'id'
    DB::table('delivery_schedules')->insert([
        'courier_id'     => $up['courier_emp'],
        'school_id'      => $up['school_id'],
        'assigned_by'    => $up['admin_user'],
        'vehicle_type'   => $up['vehicle'],
        'vehicle_plate'  => $up['plate'],
        'status'         => 'in_order',
        'scheduled_at'   => $schedAt,
        'delivery_notes' => 'Jadwal MBG besok',
        'created_at'     => now(),
        'updated_at'     => now(),
    ]);

    $totalSchedules++;
    echo "  + Upcoming: SPPG {$up['sppg_id']} → {$up['school_name']}" . PHP_EOL;
}

echo PHP_EOL . "=== SEEDING SELESAI ===" . PHP_EOL;
echo "Total Delivery Schedules inserted: {$totalSchedules}" . PHP_EOL;
echo "Total Delivery Histories inserted: {$totalHistories}" . PHP_EOL;

// Verify final counts
$dsCount = DB::table('delivery_schedules')->count();
$dhCount = DB::table('delivery_histories')->count();
$pCount  = DB::table('partners')->count();
echo PHP_EOL . "=== Total di Database ===" . PHP_EOL;
echo "delivery_schedules : {$dsCount}" . PHP_EOL;
echo "delivery_histories : {$dhCount}" . PHP_EOL;
echo "partners           : {$pCount}"  . PHP_EOL;

// Per-SPPG verification
echo PHP_EOL . "=== Per-SPPG Schedule Count ===" . PHP_EOL;
$perSppg = DB::select("
    SELECT e.sppg_id, count(*) as jml
    FROM delivery_schedules ds
    JOIN employees e ON ds.courier_id = e.id
    GROUP BY e.sppg_id
    ORDER BY e.sppg_id
");
foreach ($perSppg as $row) {
    echo "SPPG {$row->sppg_id}: {$row->jml} jadwal pengiriman" . PHP_EOL;
}
