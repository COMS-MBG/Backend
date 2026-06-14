<?php

use Carbon\Carbon;

echo "=== Seeding Delivery Schedules & Histories for SPPG 9 & 11 ===" . PHP_EOL;

// List school IDs for SPPG 9 and SPPG 11
$sppg9_school_ids = [22, 23, 24, 25];
$sppg11_school_ids = [26, 27, 28, 29];
$all_school_ids = array_merge($sppg9_school_ids, $sppg11_school_ids);

// Step 1: Clean up existing schedules/histories for these schools
echo "Cleaning up old records..." . PHP_EOL;
$deletedHistories = DB::table('delivery_histories')->whereIn('school_id', $all_school_ids)->delete();
$deletedSchedules = DB::table('delivery_schedules')->whereIn('school_id', $all_school_ids)->delete();
echo "  - Deleted {$deletedHistories} histories" . PHP_EOL;
echo "  - Deleted {$deletedSchedules} schedules" . PHP_EOL;

$deliverySetups = [
    [
        'sppg_id'    => 9,
        'admin_user' => 18,
        'couriers'   => [
            ['emp_id' => 25, 'name' => 'Asep Kurir Coblong', 'vehicle' => 'motorcycle', 'plate' => 'D 4567 OP'],
        ],
        'schools' => [
            ['id' => 22, 'name' => 'SD Negeri Coblong 1',  'address' => 'Jl. Pendidikan No. 1',                     'lat' => -6.8830, 'lng' => 107.6170],
            ['id' => 23, 'name' => 'SD Negeri Coblong 2',  'address' => 'Jl. Siliwangi No. 45, Coblong, Bandung',   'lat' => -6.8967, 'lng' => 107.6099],
            ['id' => 24, 'name' => 'SD Negeri Lebak Gede', 'address' => 'Jl. Lebak Gede No. 12, Coblong, Bandung',  'lat' => -6.8911, 'lng' => 107.6143],
            ['id' => 25, 'name' => 'SD Negeri Dago',       'address' => 'Jl. Dago Pojok No. 5, Coblong, Bandung',   'lat' => -6.8856, 'lng' => 107.6180],
        ],
    ],
    [
        'sppg_id'    => 11,
        'admin_user' => 20,
        'couriers'   => [
            ['emp_id' => 38, 'name' => 'Asep Kurir Lengkong', 'vehicle' => 'motorcycle', 'plate' => 'D 1234 LK'],
        ],
        'schools' => [
            ['id' => 26, 'name' => 'SDN Lengkong 01',          'address' => 'Jl. Lengkong Besar No. 1',           'lat' => -6.9350, 'lng' => 107.6350],
            ['id' => 27, 'name' => 'SMPN 14 Lengkong',         'address' => 'Jl. Lengkong Kecil No. 14',          'lat' => -6.9380, 'lng' => 107.6380],
            ['id' => 28, 'name' => 'SMAN 5 Lengkong',          'address' => 'Jl. Belitung No. 5, Lengkong',       'lat' => -6.9320, 'lng' => 107.6320],
            ['id' => 29, 'name' => 'SDN Cijagra 02 Lengkong',  'address' => 'Jl. Cijagra No. 2, Lengkong',        'lat' => -6.9360, 'lng' => 107.6360],
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
        $deliveriesPerDay = rand(2, min(count($schools), count($couriers) + 2)); // 2 to 3 deliveries per day

        for ($d = 0; $d < $deliveriesPerDay; $d++) {
            $courier = $couriers[$courierIdx % count($couriers)];
            $school  = $schools[$schoolIdx % count($schools)];

            $scheduledAt = $date->copy()->addHours(6)->addMinutes(rand(0, 30));
            $departedAt  = $scheduledAt->copy()->addMinutes(rand(5, 20));
            $arrivedAt   = $departedAt->copy()->addMinutes(rand(15, 45));
            $confirmedAt = $arrivedAt->copy()->addMinutes(rand(5, 30));
            $distanceKm  = round(rand(15, 80) / 10, 1);

            // Insert schedule
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

            // Insert history
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

// Upcoming schedules (status in_order)
echo PHP_EOL . "=== Adding upcoming schedules (in_order) ===" . PHP_EOL;
$upcomingSetups = [
    ['sppg_id' => 9,  'admin_user' => 18, 'courier_emp' => 25, 'vehicle' => 'motorcycle', 'plate' => 'D 4567 OP', 'school_id' => 22, 'school_name' => 'SD Negeri Coblong 1'],
    ['sppg_id' => 11, 'admin_user' => 20, 'courier_emp' => 38, 'vehicle' => 'motorcycle', 'plate' => 'D 1234 LK', 'school_id' => 26, 'school_name' => 'SDN Lengkong 01'],
    ['sppg_id' => 11, 'admin_user' => 20, 'courier_emp' => 38, 'vehicle' => 'motorcycle', 'plate' => 'D 1234 LK', 'school_id' => 27, 'school_name' => 'SMPN 14 Lengkong'],
];

foreach ($upcomingSetups as $up) {
    $schedAt = Carbon::tomorrow()->setTime(7, 0);

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

echo PHP_EOL . "=== SEEDING SPPG 9 & 11 SELESAI ===" . PHP_EOL;
echo "Total Delivery Schedules inserted: {$totalSchedules}" . PHP_EOL;
echo "Total Delivery Histories inserted: {$totalHistories}" . PHP_EOL;

// Per-SPPG verification query
$perSppg = DB::select("
    SELECT e.sppg_id, count(*) as jml
    FROM delivery_schedules ds
    JOIN employees e ON ds.courier_id = e.id
    WHERE e.sppg_id IN (9, 11)
    GROUP BY e.sppg_id
    ORDER BY e.sppg_id
");
foreach ($perSppg as $row) {
    echo "SPPG {$row->sppg_id}: {$row->jml} jadwal pengiriman" . PHP_EOL;
}
