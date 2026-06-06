<?php

namespace Database\Seeders;

use App\Models\DeliveryHistory;
use App\Models\DeliverySchedule;
use App\Models\Employee;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DeliveryScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil data dari database
        $adminLogistik = User::where('email', 'adit@sppg.test')->first();
        $adminSppg     = User::where('email', 'naufal@sppg.test')->first();
        
        $courierAsep    = Employee::where('position', 'kurir')->where('name', 'like', '%Asep%')->first();
        $courierBambang = Employee::where('position', 'kurir')->where('name', 'like', '%Bambang%')->first();

        $schools = School::whereNotNull('sppg_id')->get();

        if (!$adminLogistik || !$courierAsep || $schools->isEmpty()) {
            $this->command->error('Gagal menjalankan DeliveryScheduleSeeder: Ketergantungan data (admin logistik, kurir, sekolah ter-assign) tidak ditemukan. Pastikan UserSeeder dan SchoolSeeder sudah dijalankan.');
            return;
        }

        $now = Carbon::now();

        // ────────── 1. STATUS: IN_ORDER (DRAFT JADWAL) ──────────
        DeliverySchedule::create([
            'courier_id'     => $courierAsep->id,
            'school_id'      => $schools->get(0)->id,
            'assigned_by'    => $adminLogistik->id,
            'submitted_by'   => null,
            'vehicle_type'   => 'motorcycle',
            'vehicle_plate'  => 'D 1234 ABC',
            'status'         => 'in_order',
            'scheduled_at'   => $now->copy()->addHours(2),
            'delivery_notes' => 'Tolong bawa kotak cadangan.',
        ]);

        // ────────── 2. STATUS: IN_ORDER & SUBMITTED (DIKIRIM KE KURIR) ──────────
        DeliverySchedule::create([
            'courier_id'     => $courierBambang->id ?? $courierAsep->id,
            'school_id'      => $schools->get(1)->id,
            'assigned_by'    => $adminLogistik->id,
            'submitted_by'   => $adminSppg->id,
            'vehicle_type'   => 'car',
            'vehicle_plate'  => 'D 5678 XYZ',
            'status'         => 'in_order', // Status di db tetap in_order sampai kurir terima/tolak
            'scheduled_at'   => $now->copy()->addHour(),
            'delivery_notes' => 'Antar lewat gerbang samping.',
        ]);

        // ────────── 3. STATUS: DELIVERING (SEDANG DIANTAR) ──────────
        $deliveringSchedule = DeliverySchedule::create([
            'courier_id'     => $courierAsep->id,
            'school_id'      => $schools->get(2)->id ?? $schools->get(0)->id,
            'assigned_by'    => $adminLogistik->id,
            'submitted_by'   => $adminSppg->id,
            'vehicle_type'   => 'motorcycle',
            'vehicle_plate'  => 'D 1234 ABC',
            'status'         => 'delivering',
            'scheduled_at'   => $now->copy()->subMinutes(30),
            'departed_at'    => $now->copy()->subMinutes(25),
            'delivery_notes' => 'Makanan hangat, hati-hati di jalan.',
            'route_snapshot' => [
                'coordinates' => [
                    [107.6147, -6.8862], // Titik awal (depot)
                    [107.6120, -6.9050], // Posisi saat ini
                ]
            ],
        ]);

        // ────────── 4. STATUS: DELIVERED (SAMPAI - MENUNGGU KONFIRMASI) ──────────
        DeliverySchedule::create([
            'courier_id'         => $courierBambang->id ?? $courierAsep->id,
            'school_id'          => $schools->get(0)->id,
            'assigned_by'        => $adminLogistik->id,
            'submitted_by'       => $adminSppg->id,
            'vehicle_type'       => 'van',
            'vehicle_plate'      => 'D 9999 VIP',
            'status'             => 'delivered',
            'scheduled_at'       => $now->copy()->subHours(2),
            'departed_at'        => $now->copy()->subHours(1)->subMinutes(50),
            'arrived_at'         => $now->copy()->subHours(1)->subMinutes(20),
            'proof_photo_path'   => 'delivery/proofs/mock_proof.jpg',
            'proof_submitted_at' => $now->copy()->subHours(1)->subMinutes(15),
            'delivery_notes'     => 'Serahkan ke TU sekolah.',
        ]);

        // ────────── 5. STATUS: REJECTED (DITOLAK KURIR) ──────────
        DeliverySchedule::create([
            'courier_id'           => $courierAsep->id,
            'school_id'            => $schools->get(1)->id,
            'assigned_by'          => $adminLogistik->id,
            'submitted_by'         => $adminSppg->id,
            'vehicle_type'         => 'motorcycle',
            'vehicle_plate'        => 'D 1234 ABC',
            'status'               => 'rejected',
            'scheduled_at'         => $now->copy()->subHours(3),
            'rejected_at'          => $now->copy()->subHours(2)->subMinutes(45),
            'rejection_reason'     => 'Ban motor bocor total di tanjakan Dago.',
            'rejection_photo_path' => 'delivery/rejections/mock_flat_tire.jpg',
        ]);

        // ────────── 6. STATUS: REVISION_REQUIRED (ADMIN MINTA UPLOAD ULANG) ──────────
        DeliverySchedule::create([
            'courier_id'         => $courierBambang->id ?? $courierAsep->id,
            'school_id'          => $schools->get(2)->id ?? $schools->get(0)->id,
            'assigned_by'        => $adminLogistik->id,
            'submitted_by'       => $adminSppg->id,
            'vehicle_type'       => 'motorcycle',
            'vehicle_plate'      => 'D 5678 XYZ',
            'status'             => 'revision_required',
            'scheduled_at'       => $now->copy()->subHours(4),
            'departed_at'        => $now->copy()->subHours(3)->subMinutes(40),
            'arrived_at'         => $now->copy()->subHours(3)->subMinutes(10),
            'proof_photo_path'   => 'delivery/proofs/mock_blurry_proof.jpg',
            'proof_submitted_at' => $now->copy()->subHours(3)->subMinutes(5),
            'confirmed_by'       => $adminLogistik->id, // Yang meminta revisi
            'confirmation_notes' => 'Foto terlalu buram dan tidak memperlihatkan serah terima dengan panitia sekolah. Tolong upload ulang foto yang jelas.',
        ]);

        // ────────── 7. STATUS: CONFIRMED (SELESAI & MASUK HISTORIES) ──────────
        $confirmedSchedule = DeliverySchedule::create([
            'courier_id'         => $courierAsep->id,
            'school_id'          => $schools->get(0)->id,
            'assigned_by'        => $adminLogistik->id,
            'submitted_by'       => $adminSppg->id,
            'vehicle_type'       => 'motorcycle',
            'vehicle_plate'      => 'D 1234 ABC',
            'status'             => 'confirmed',
            'scheduled_at'       => $now->copy()->subDays(1)->setHour(10)->setMinute(0),
            'departed_at'        => $now->copy()->subDays(1)->setHour(9)->setMinute(45),
            'arrived_at'         => $now->copy()->subDays(1)->setHour(10)->setMinute(12),
            'proof_photo_path'   => 'delivery/proofs/mock_confirmed_proof.jpg',
            'proof_submitted_at' => $now->copy()->subDays(1)->setHour(10)->setMinute(15),
            'confirmed_by'       => $adminLogistik->id,
            'confirmed_at'       => $now->copy()->subDays(1)->setHour(10)->setMinute(30),
            'confirmation_notes' => 'Serah terima terverifikasi sukses.',
            'route_snapshot'     => [
                'coordinates' => [
                    [107.6147, -6.8862], // Depot
                    [107.6120, -6.9050],
                    [107.6050, -6.8920], // Tujuan
                ]
            ],
        ]);

        // Create the corresponding DeliveryHistory entry (Immutable log)
        DeliveryHistory::create([
            'delivery_schedule_id' => $confirmedSchedule->id,
            'courier_id'           => $confirmedSchedule->courier_id,
            'school_id'            => $confirmedSchedule->school_id,
            'courier_name'         => $confirmedSchedule->courier->name ?? 'Asep Kurir',
            'school_name'          => $confirmedSchedule->school->nama ?? 'Sekolah Mitra',
            'school_address'       => $confirmedSchedule->school->alamat ?? 'Alamat Sekolah',
            'vehicle_type'         => $confirmedSchedule->vehicle_type,
            'vehicle_plate'        => $confirmedSchedule->vehicle_plate,
            'departed_at'          => $confirmedSchedule->departed_at,
            'arrived_at'           => $confirmedSchedule->arrived_at,
            'proof_photo_path'     => $confirmedSchedule->proof_photo_path,
            'route_snapshot'       => $confirmedSchedule->route_snapshot,
            'distance_km'          => 4.25,
            'confirmed_by'         => $adminLogistik->id,
            'confirmed_at'         => $confirmedSchedule->confirmed_at,
            'notes'                => $confirmedSchedule->confirmation_notes,
        ]);

        $this->command->info('DeliverySchedules & DeliveryHistories seed selesai.');
    }
}
