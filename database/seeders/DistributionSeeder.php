<?php

namespace Database\Seeders;

use App\Models\CourierLocation;
use App\Models\DeliveryHistory;
use App\Models\DeliverySchedule;
use App\Models\Employee;
use App\Models\Role;
use App\Models\School;
use App\Models\SPPG;
use App\Models\User;
use Illuminate\Database\Seeder;

class DistributionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dapatkan SPPG & Roles
        $sppg = SPPG::first();
        if (!$sppg) {
            $this->command->error('Harap jalankan SPPGSeeder terlebih dahulu!');
            return;
        }

        $courierRole = Role::where('slug', 'kurir')->first();
        if (!$courierRole) {
            $this->command->error('Harap jalankan RoleSeeder terlebih dahulu!');
            return;
        }

        $adminLogistikRole = Role::where('slug', 'admin-logistik')->first();
        $adminSppgRole = Role::where('slug', 'admin-sppg')->first();

        // 2. Pastikan ada user Admin Logistik & Admin SPPG
        $adminLogistik = User::whereHas('employee', function($q) use ($adminLogistikRole) {
            $q->where('role_id', $adminLogistikRole?->id);
        })->first() ?? User::where('email', 'adit@sppg.test')->first();

        $adminSppg = User::whereHas('employee', function($q) use ($adminSppgRole) {
            $q->where('role_id', $adminSppgRole?->id);
        })->first() ?? User::where('email', 'naufal@sppg.test')->first();

        if (!$adminLogistik || !$adminSppg) {
            $this->command->error('Harap jalankan UserSeeder terlebih dahulu!');
            return;
        }

        // 3. Pastikan ada data Kurir (User & Employee) - 5 Kurir
        $couriersData = [
            [
                'name' => 'Agus Kurir',
                'email' => 'agus@sppg.test',
                'phone' => '081234567890',
                'nik' => '3201010101010002',
                'vehicle_type' => 'motorcycle',
                'vehicle_plate' => 'D 1234 AB'
            ],
            [
                'name' => 'Budi Kurir',
                'email' => 'budi.kurir@sppg.test',
                'phone' => '081234567891',
                'nik' => '3201010101010003',
                'vehicle_type' => 'van',
                'vehicle_plate' => 'D 5678 CD'
            ],
            [
                'name' => 'Candra Kurir',
                'email' => 'candra.kurir@sppg.test',
                'phone' => '081234567892',
                'nik' => '3201010101010004',
                'vehicle_type' => 'motorcycle',
                'vehicle_plate' => 'D 9012 EF'
            ],
            [
                'name' => 'Dedi Kurir',
                'email' => 'dedi.kurir@sppg.test',
                'phone' => '081234567893',
                'nik' => '3201010101010005',
                'vehicle_type' => 'van',
                'vehicle_plate' => 'D 3456 GH'
            ],
            [
                'name' => 'Eko Kurir',
                'email' => 'eko.kurir@sppg.test',
                'phone' => '081234567894',
                'nik' => '3201010101010006',
                'vehicle_type' => 'motorcycle',
                'vehicle_plate' => 'D 7890 IJ'
            ],
        ];

        $courierEmployees = [];

        foreach ($couriersData as $cData) {
            // Cari atau buat user
            $user = User::firstOrCreate(
                ['email' => $cData['email']],
                [
                    'name' => $cData['name'],
                    'password' => 'password123',
                    'phone' => $cData['phone'],
                    'is_active' => true,
                    'role_type' => 'sppg_user',
                    'sppg_id' => $sppg->id,
                    'email_verified_at' => now(),
                ]
            );

            // Cari atau buat employee
            $employee = Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'sppg_id' => $sppg->id,
                    'user_id' => $user->id,
                    'role_id' => $courierRole->id,
                    'name' => $cData['name'],
                    'nik' => $cData['nik'],
                    'position' => 'kurir',
                    'phone' => $cData['phone'],
                    'status' => 'active',
                    'joined_at' => now()->subMonths(3),
                ]
            );

            $courierEmployees[] = [
                'employee' => $employee,
                'vehicle_type' => $cData['vehicle_type'],
                'vehicle_plate' => $cData['vehicle_plate']
            ];

            $this->command->info("Courier '{$cData['name']}' ready.");
        }

        // 4. Dapatkan sekolah-sekolah untuk pengiriman
        if (School::count() === 0) {
            $this->command->info('Seeding schools first...');
            $this->call(SchoolSeeder::class);
        }

        $schools = School::whereNotNull('sppg_id')->get();
        if ($schools->isEmpty()) {
            $schools = School::all();
        }

        if ($schools->isEmpty()) {
            $this->command->error('Tidak ada sekolah untuk diseed!');
            return;
        }

        // Hapus data distribusi lama agar bersih saat seeder dijalankan ulang
        CourierLocation::query()->delete();
        DeliveryHistory::query()->delete();
        DeliverySchedule::query()->delete();

        // 5. SEED DATA HISTORIS (CONFIRMED) & HISTORY LOG (12 data dari 7 hari terakhir)
        $routeSnapshotBase = [
            'coordinates' => [
                [107.5908, -6.8798],
                [107.5950, -6.8810],
                [107.6020, -6.8830],
                [107.6090, -6.8850],
                [107.6147, -6.8862]
            ]
        ];

        for ($i = 1; $i <= 12; $i++) {
            $cIdx = ($i - 1) % count($courierEmployees);
            $sIdx = ($i + 2) % $schools->count();
            $courier = $courierEmployees[$cIdx];
            $school = $schools->get($sIdx);

            $daysAgo = (int)ceil($i / 2); // Rentang 1 s.d. 6 hari yang lalu
            $schedTime = now()->subDays($daysAgo)->setHour(7 + ($i % 3))->setMinute(0);
            $departTime = $schedTime->copy()->addMinutes(10);
            $arriveTime = $departTime->copy()->addMinutes(25 + ($i * 2));
            $confirmTime = $arriveTime->copy()->addMinutes(15);

            $schedule = DeliverySchedule::create([
                'courier_id' => $courier['employee']->id,
                'school_id' => $school->id,
                'assigned_by' => $adminLogistik->id,
                'submitted_by' => $adminSppg->id,
                'vehicle_type' => $courier['vehicle_type'],
                'vehicle_plate' => $courier['vehicle_plate'],
                'status' => DeliverySchedule::STATUS_CONFIRMED,
                'scheduled_at' => $schedTime,
                'departed_at' => $departTime,
                'arrived_at' => $arriveTime,
                'proof_photo_path' => "delivery/proofs/dummy_proof_confirmed_{$i}.jpg",
                'proof_submitted_at' => $arriveTime->copy()->addMinutes(5),
                'confirmed_by' => $adminLogistik->id,
                'confirmed_at' => $confirmTime,
                'confirmation_notes' => 'Pengiriman telah selesai dan terverifikasi dengan baik.',
                'route_snapshot' => $routeSnapshotBase,
                'delivery_notes' => 'Harap diserahkan kepada penanggung jawab konsumsi sekolah.',
            ]);

            DeliveryHistory::create([
                'delivery_schedule_id' => $schedule->id,
                'courier_id' => $schedule->courier_id,
                'school_id' => $schedule->school_id,
                'courier_name' => $courier['employee']->name,
                'school_name' => $school->nama,
                'school_address' => $school->alamat,
                'vehicle_type' => $schedule->vehicle_type,
                'vehicle_plate' => $schedule->vehicle_plate,
                'departed_at' => $schedule->departed_at,
                'arrived_at' => $schedule->arrived_at,
                'proof_photo_path' => $schedule->proof_photo_path,
                'route_snapshot' => $routeSnapshotBase,
                'distance_km' => 3.5 + ($i * 0.4),
                'confirmed_by' => $adminLogistik->id,
                'confirmed_at' => $schedule->confirmed_at,
                'notes' => 'Makanan tiba tepat waktu dan diterima dalam kondisi baik.',
            ]);
        }
        $this->command->info('Created 12 historical delivery records & schedules.');

        // 6. SEED DATA HARI INI & MENDATANG (BERBAGAI STATUS)

        // a. STATUS: in_order (Draft oleh Admin Logistik, belum disubmit ke kurir) - 2 data
        DeliverySchedule::create([
            'courier_id' => $courierEmployees[0]['employee']->id,
            'school_id' => $schools->get(0)->id,
            'assigned_by' => $adminLogistik->id,
            'submitted_by' => null,
            'vehicle_type' => $courierEmployees[0]['vehicle_type'],
            'vehicle_plate' => $courierEmployees[0]['vehicle_plate'],
            'status' => DeliverySchedule::STATUS_IN_ORDER,
            'scheduled_at' => now()->addDay()->setHour(7)->setMinute(0),
            'delivery_notes' => 'Pengiriman makanan bergizi rutin untuk besok pagi. Harap bawa termos nasi tambahan.',
        ]);

        DeliverySchedule::create([
            'courier_id' => $courierEmployees[2]['employee']->id,
            'school_id' => $schools->get(4 % $schools->count())->id,
            'assigned_by' => $adminLogistik->id,
            'submitted_by' => null,
            'vehicle_type' => $courierEmployees[2]['vehicle_type'],
            'vehicle_plate' => $courierEmployees[2]['vehicle_plate'],
            'status' => DeliverySchedule::STATUS_IN_ORDER,
            'scheduled_at' => now()->addDay()->setHour(8)->setMinute(30),
            'delivery_notes' => 'Makanan bergizi pagi. Hati-hati di jalan.',
        ]);
        $this->command->info('Created 2 draft schedules.');

        // b. STATUS: in_order (Telah disubmit oleh Admin SPPG ke Kurir, menunggu aksi kurir) - 3 data
        DeliverySchedule::create([
            'courier_id' => $courierEmployees[0]['employee']->id,
            'school_id' => $schools->get(1)->id,
            'assigned_by' => $adminLogistik->id,
            'submitted_by' => $adminSppg->id,
            'vehicle_type' => $courierEmployees[0]['vehicle_type'],
            'vehicle_plate' => $courierEmployees[0]['vehicle_plate'],
            'status' => DeliverySchedule::STATUS_IN_ORDER,
            'scheduled_at' => now()->setHour(9)->setMinute(30),
            'delivery_notes' => 'Makanan bergizi siang. Koordinasikan dengan satpam sekolah saat tiba.',
        ]);

        DeliverySchedule::create([
            'courier_id' => $courierEmployees[3]['employee']->id,
            'school_id' => $schools->get(5 % $schools->count())->id,
            'assigned_by' => $adminLogistik->id,
            'submitted_by' => $adminSppg->id,
            'vehicle_type' => $courierEmployees[3]['vehicle_type'],
            'vehicle_plate' => $courierEmployees[3]['vehicle_plate'],
            'status' => DeliverySchedule::STATUS_IN_ORDER,
            'scheduled_at' => now()->setHour(10)->setMinute(0),
            'delivery_notes' => 'Pengiriman makanan siang sekolah. Gunakan pintu belakang sekolah.',
        ]);

        DeliverySchedule::create([
            'courier_id' => $courierEmployees[4]['employee']->id,
            'school_id' => $schools->get(8 % $schools->count())->id,
            'assigned_by' => $adminLogistik->id,
            'submitted_by' => $adminSppg->id,
            'vehicle_type' => $courierEmployees[4]['vehicle_type'],
            'vehicle_plate' => $courierEmployees[4]['vehicle_plate'],
            'status' => DeliverySchedule::STATUS_IN_ORDER,
            'scheduled_at' => now()->addDay()->setHour(9)->setMinute(0),
            'delivery_notes' => 'Jadwal pengiriman besok siang.',
        ]);
        $this->command->info('Created 3 submitted schedules.');

        // c. STATUS: delivering (Sedang mengirim, dengan koordinat GPS real-time) - 3 data
        
        // Kurir 1 (Budi - Van) -> School 2 (SMPN 5 Bandung)
        $scheduleDel1 = DeliverySchedule::create([
            'courier_id' => $courierEmployees[1]['employee']->id,
            'school_id' => $schools->get(2)->id,
            'assigned_by' => $adminLogistik->id,
            'submitted_by' => $adminSppg->id,
            'vehicle_type' => $courierEmployees[1]['vehicle_type'],
            'vehicle_plate' => $courierEmployees[1]['vehicle_plate'],
            'status' => DeliverySchedule::STATUS_DELIVERING,
            'scheduled_at' => now()->subMinutes(20),
            'departed_at' => now()->subMinutes(15),
            'delivery_notes' => 'Pengiriman siang. Hindari jalan macet di Pasteur.',
        ]);

        $locsDel1 = [
            ['lat' => -6.8798, 'lng' => 107.5908, 'speed' => 0.0, 'heading' => 0.0, 'time' => now()->subMinutes(15)],
            ['lat' => -6.8810, 'lng' => 107.5950, 'speed' => 35.2, 'heading' => 110.0, 'time' => now()->subMinutes(10)],
            ['lat' => -6.8830, 'lng' => 107.6020, 'speed' => 42.1, 'heading' => 112.0, 'time' => now()->subMinutes(5)],
            ['lat' => -6.8850, 'lng' => 107.6090, 'speed' => 20.0, 'heading' => 90.0, 'time' => now()],
        ];

        foreach ($locsDel1 as $loc) {
            CourierLocation::create([
                'delivery_schedule_id' => $scheduleDel1->id,
                'courier_id' => $courierEmployees[1]['employee']->id,
                'latitude' => $loc['lat'],
                'longitude' => $loc['lng'],
                'speed_kmh' => $loc['speed'],
                'heading_degrees' => $loc['heading'],
                'accuracy_meters' => 5.0,
                'recorded_at' => $loc['time'],
            ]);
        }

        // Kurir 2 (Candra - Motor) -> School 3 (SDN 024 Coblong)
        $scheduleDel2 = DeliverySchedule::create([
            'courier_id' => $courierEmployees[2]['employee']->id,
            'school_id' => $schools->get(3 % $schools->count())->id,
            'assigned_by' => $adminLogistik->id,
            'submitted_by' => $adminSppg->id,
            'vehicle_type' => $courierEmployees[2]['vehicle_type'],
            'vehicle_plate' => $courierEmployees[2]['vehicle_plate'],
            'status' => DeliverySchedule::STATUS_DELIVERING,
            'scheduled_at' => now()->subMinutes(15),
            'departed_at' => now()->subMinutes(10),
            'delivery_notes' => 'Makanan bergizi SDN Coblong. Bawa jas hujan.',
        ]);

        $locsDel2 = [
            ['lat' => -6.8798, 'lng' => 107.5908, 'speed' => 0.0, 'heading' => 0.0, 'time' => now()->subMinutes(10)],
            ['lat' => -6.8812, 'lng' => 107.6000, 'speed' => 40.5, 'heading' => 85.0, 'time' => now()->subMinutes(5)],
            ['lat' => -6.8825, 'lng' => 107.6100, 'speed' => 45.0, 'heading' => 90.0, 'time' => now()],
        ];

        foreach ($locsDel2 as $loc) {
            CourierLocation::create([
                'delivery_schedule_id' => $scheduleDel2->id,
                'courier_id' => $courierEmployees[2]['employee']->id,
                'latitude' => $loc['lat'],
                'longitude' => $loc['lng'],
                'speed_kmh' => $loc['speed'],
                'heading_degrees' => $loc['heading'],
                'accuracy_meters' => 4.0,
                'recorded_at' => $loc['time'],
            ]);
        }

        // Kurir 4 (Eko - Motor) -> School 7 (SMAN 11 Bandung)
        $scheduleDel3 = DeliverySchedule::create([
            'courier_id' => $courierEmployees[4]['employee']->id,
            'school_id' => $schools->get(7 % $schools->count())->id,
            'assigned_by' => $adminLogistik->id,
            'submitted_by' => $adminSppg->id,
            'vehicle_type' => $courierEmployees[4]['vehicle_type'],
            'vehicle_plate' => $courierEmployees[4]['vehicle_plate'],
            'status' => DeliverySchedule::STATUS_DELIVERING,
            'scheduled_at' => now()->subMinutes(25),
            'departed_at' => now()->subMinutes(20),
            'delivery_notes' => 'Bawa makanan ke kantin belakang sekolah.',
        ]);

        $locsDel3 = [
            ['lat' => -6.9500, 'lng' => 107.6200, 'speed' => 0.0, 'heading' => 0.0, 'time' => now()->subMinutes(20)],
            ['lat' => -6.9470, 'lng' => 107.6220, 'speed' => 38.0, 'heading' => 20.0, 'time' => now()->subMinutes(15)],
            ['lat' => -6.9430, 'lng' => 107.6240, 'speed' => 41.5, 'heading' => 25.0, 'time' => now()->subMinutes(10)],
            ['lat' => -6.9400, 'lng' => 107.6260, 'speed' => 30.2, 'heading' => 10.0, 'time' => now()->subMinutes(5)],
            ['lat' => -6.9385, 'lng' => 107.6270, 'speed' => 12.0, 'heading' => 350.0, 'time' => now()],
        ];

        foreach ($locsDel3 as $loc) {
            CourierLocation::create([
                'delivery_schedule_id' => $scheduleDel3->id,
                'courier_id' => $courierEmployees[4]['employee']->id,
                'latitude' => $loc['lat'],
                'longitude' => $loc['lng'],
                'speed_kmh' => $loc['speed'],
                'heading_degrees' => $loc['heading'],
                'accuracy_meters' => 3.0,
                'recorded_at' => $loc['time'],
            ]);
        }
        $this->command->info('Created 3 active delivering schedules with GPS trails.');

        // d. STATUS: delivered (Selesai kirim, menunggu konfirmasi admin) - 3 data
        DeliverySchedule::create([
            'courier_id' => $courierEmployees[0]['employee']->id,
            'school_id' => $schools->get(3 % $schools->count())->id,
            'assigned_by' => $adminLogistik->id,
            'submitted_by' => $adminSppg->id,
            'vehicle_type' => $courierEmployees[0]['vehicle_type'],
            'vehicle_plate' => $courierEmployees[0]['vehicle_plate'],
            'status' => DeliverySchedule::STATUS_DELIVERED,
            'scheduled_at' => now()->subHours(1),
            'departed_at' => now()->subHours(1)->addMinutes(10),
            'arrived_at' => now()->subMinutes(25),
            'proof_photo_path' => 'delivery/proofs/dummy_proof_delivered_1.jpg',
            'proof_submitted_at' => now()->subMinutes(20),
            'delivery_notes' => 'Pengiriman pagi sekali. Anak-anak ada kegiatan senam.',
        ]);

        DeliverySchedule::create([
            'courier_id' => $courierEmployees[3]['employee']->id,
            'school_id' => $schools->get(6 % $schools->count())->id,
            'assigned_by' => $adminLogistik->id,
            'submitted_by' => $adminSppg->id,
            'vehicle_type' => $courierEmployees[3]['vehicle_type'],
            'vehicle_plate' => $courierEmployees[3]['vehicle_plate'],
            'status' => DeliverySchedule::STATUS_DELIVERED,
            'scheduled_at' => now()->subHours(2),
            'departed_at' => now()->subHours(2)->addMinutes(5),
            'arrived_at' => now()->subHours(1)->subMinutes(10),
            'proof_photo_path' => 'delivery/proofs/dummy_proof_delivered_2.jpg',
            'proof_submitted_at' => now()->subHours(1)->subMinutes(5),
            'delivery_notes' => 'Porsi gizi lengkap.',
        ]);

        DeliverySchedule::create([
            'courier_id' => $courierEmployees[1]['employee']->id,
            'school_id' => $schools->get(9 % $schools->count())->id,
            'assigned_by' => $adminLogistik->id,
            'submitted_by' => $adminSppg->id,
            'vehicle_type' => $courierEmployees[1]['vehicle_type'],
            'vehicle_plate' => $courierEmployees[1]['vehicle_plate'],
            'status' => DeliverySchedule::STATUS_DELIVERED,
            'scheduled_at' => now()->subHours(3),
            'departed_at' => now()->subHours(3)->addMinutes(12),
            'arrived_at' => now()->subHours(2)->subMinutes(15),
            'proof_photo_path' => 'delivery/proofs/dummy_proof_delivered_3.jpg',
            'proof_submitted_at' => now()->subHours(2)->subMinutes(10),
            'delivery_notes' => 'Makanan box.',
        ]);
        $this->command->info('Created 3 delivered schedules (waiting confirmation).');

        // e. STATUS: revision_required (Admin meminta revisi bukti) - 2 data
        DeliverySchedule::create([
            'courier_id' => $courierEmployees[0]['employee']->id,
            'school_id' => $schools->get(4 % $schools->count())->id,
            'assigned_by' => $adminLogistik->id,
            'submitted_by' => $adminSppg->id,
            'vehicle_type' => $courierEmployees[0]['vehicle_type'],
            'vehicle_plate' => $courierEmployees[0]['vehicle_plate'],
            'status' => DeliverySchedule::STATUS_REVISION_REQUIRED,
            'scheduled_at' => now()->subHours(3),
            'departed_at' => now()->subHours(3)->addMinutes(5),
            'arrived_at' => now()->subHours(2),
            'proof_photo_path' => 'delivery/proofs/dummy_proof_revision_1.jpg',
            'proof_submitted_at' => now()->subHours(2)->addMinutes(10),
            'confirmed_by' => $adminLogistik->id,
            'confirmation_notes' => 'Foto bukti buram dan tidak terlihat jelas wajah guru penerimanya. Harap unggah ulang.',
            'delivery_notes' => 'Harap berfoto bersama guru piket saat menyerahkan makanan.',
        ]);

        DeliverySchedule::create([
            'courier_id' => $courierEmployees[2]['employee']->id,
            'school_id' => $schools->get(10 % $schools->count())->id,
            'assigned_by' => $adminLogistik->id,
            'submitted_by' => $adminSppg->id,
            'vehicle_type' => $courierEmployees[2]['vehicle_type'],
            'vehicle_plate' => $courierEmployees[2]['vehicle_plate'],
            'status' => DeliverySchedule::STATUS_REVISION_REQUIRED,
            'scheduled_at' => now()->subHours(4),
            'departed_at' => now()->subHours(4)->addMinutes(8),
            'arrived_at' => now()->subHours(3),
            'proof_photo_path' => 'delivery/proofs/dummy_proof_revision_2.jpg',
            'proof_submitted_at' => now()->subHours(3)->addMinutes(5),
            'confirmed_by' => $adminLogistik->id,
            'confirmation_notes' => 'Jumlah box makanan di foto bukti serah terima tidak sesuai dengan jumlah porsi pesanan. Mohon revisi.',
            'delivery_notes' => 'Sediakan makanan box.',
        ]);
        $this->command->info('Created 2 revision requested schedules.');

        // f. STATUS: rejected (Kurir menolak tugas) - 2 data
        DeliverySchedule::create([
            'courier_id' => $courierEmployees[1]['employee']->id,
            'school_id' => $schools->get(5 % $schools->count())->id,
            'assigned_by' => $adminLogistik->id,
            'submitted_by' => $adminSppg->id,
            'vehicle_type' => $courierEmployees[1]['vehicle_type'],
            'vehicle_plate' => $courierEmployees[1]['vehicle_plate'],
            'status' => DeliverySchedule::STATUS_REJECTED,
            'scheduled_at' => now()->subHours(5),
            'rejection_reason' => 'Ban belakang mobil box bocor di daerah Pasteur dan membutuhkan derek.',
            'rejection_photo_path' => 'delivery/rejections/dummy_rejection_1.jpg',
            'rejected_at' => now()->subHours(4)->subMinutes(45),
            'delivery_notes' => 'Hati-hati dengan jalanan licin.',
        ]);

        DeliverySchedule::create([
            'courier_id' => $courierEmployees[4]['employee']->id,
            'school_id' => $schools->get(11 % $schools->count())->id,
            'assigned_by' => $adminLogistik->id,
            'submitted_by' => $adminSppg->id,
            'vehicle_type' => $courierEmployees[4]['vehicle_type'],
            'vehicle_plate' => $courierEmployees[4]['vehicle_plate'],
            'status' => DeliverySchedule::STATUS_REJECTED,
            'scheduled_at' => now()->subHours(2),
            'rejection_reason' => 'Motor kurir mengalami gangguan kelistrikan dan mogok total.',
            'rejection_photo_path' => 'delivery/rejections/dummy_rejection_2.jpg',
            'rejected_at' => now()->subHours(1)->subMinutes(50),
            'delivery_notes' => 'Pengiriman makanan box.',
        ]);
        $this->command->info('Created 2 rejected schedules.');

        $this->command->info('Distribution seeding completed successfully!');
    }
}
