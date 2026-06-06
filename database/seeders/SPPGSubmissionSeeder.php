<?php

namespace Database\Seeders;

use App\Models\SppgDraft;
use App\Models\SppgDraftPartner;
use App\Models\User;
use Illuminate\Database\Seeder;

class SPPGSubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean existing drafts
        SppgDraftPartner::query()->delete();
        SppgDraft::query()->delete();

        $superadmin = User::where('role_type', 'super_admin')->first();
        $userId = $superadmin ? $superadmin->id : 1;

        // Draft 1: Baru (Belum dikonfirmasi di peta)
        $draft1 = SppgDraft::create([
            'submission_number' => 'DRAFT-20260607-001',
            'submitted_by' => $userId,
            'source' => 'internal',
            'form1_data' => [
                'name' => 'SPPG Lembang',
                'address' => 'Jl. Raya Lembang No. 12',
                'district' => 'Lembang',
                'city' => 'Bandung Barat',
                'province' => 'Jawa Barat',
                'capacity' => 15,
            ],
            'form2_data' => [
                'name' => 'Ahmad Admin',
                'email' => 'ahmad@sppg.test',
                'password' => 'password123',
            ],
            'form3_data' => [
                'nutritionist' => [
                    'name' => 'Siti Gizi',
                    'email' => 'siti.gizi@sppg.test',
                    'password' => 'password123',
                ],
                'logistics_admin' => [
                    'name' => 'Budi Logistik',
                    'email' => 'budi.log@sppg.test',
                    'password' => 'password123',
                ],
            ],
            'latitude' => -6.8150,
            'longitude' => 107.6100,
            'confirmed_latitude' => null,
            'confirmed_longitude' => null,
            'point_status' => 'green',
            'map_confirmed' => false,
            'status' => 'draft',
            'submitted_at' => null,
        ]);

        $draft1->partners()->createMany([
            [
                'school_name' => 'SMA Negeri 1 Lembang',
                'npsn' => '20206001',
                'level' => 'SMA',
                'school_status' => 'public',
                'address' => 'Jl. Maribaya No. 18',
                'city' => 'Bandung Barat',
                'district' => 'Lembang',
                'latitude' => -6.8180,
                'longitude' => 107.6210,
                'jumlah_porsi' => 850,
                'data_source' => 'database',
            ],
            [
                'school_name' => 'SMP Negeri 1 Lembang',
                'npsn' => '20206002',
                'level' => 'SMP',
                'school_status' => 'public',
                'address' => 'Jl. Raya Lembang No. 150',
                'city' => 'Bandung Barat',
                'district' => 'Lembang',
                'latitude' => -6.8120,
                'longitude' => 107.6120,
                'jumlah_porsi' => 600,
                'data_source' => 'database',
            ],
        ]);

        // Draft 2: Konfirmasi Peta, Konflik Sedang (Status Kuning)
        $draft2 = SppgDraft::create([
            'submission_number' => 'DRAFT-20260607-002',
            'submitted_by' => $userId,
            'source' => 'internal',
            'form1_data' => [
                'name' => 'SPPG Pasteur Cidadap',
                'address' => 'Jl. Dr. Djunjunan No. 120',
                'district' => 'Sukajadi',
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'capacity' => 10,
            ],
            'form2_data' => [
                'name' => 'Diana Admin',
                'email' => 'diana@sppg.test',
                'password' => 'password123',
            ],
            'form3_data' => [
                'nutritionist' => [
                    'name' => 'Novi Gizi',
                    'email' => 'novi.gizi@sppg.test',
                    'password' => 'password123',
                ],
            ],
            'latitude' => -6.8910,
            'longitude' => 107.5850,
            'confirmed_latitude' => -6.8920,
            'confirmed_longitude' => 107.5840,
            'point_status' => 'yellow',
            'map_confirmed' => true,
            'status' => 'draft',
            'submitted_at' => null,
        ]);

        $draft2->partners()->createMany([
            [
                'school_name' => 'SMA Negeri 9 Bandung',
                'npsn' => '20206003',
                'level' => 'SMA',
                'school_status' => 'public',
                'address' => 'Jl. Suparmin No.1A',
                'city' => 'Bandung',
                'district' => 'Cicendo',
                'latitude' => -6.9002,
                'longitude' => 107.5785,
                'jumlah_porsi' => 880,
                'data_source' => 'database',
            ],
        ]);

        // Draft 3: Sudah Disubmit (Registered)
        $draft3 = SppgDraft::create([
            'submission_number' => 'DRAFT-20260606-003',
            'submitted_by' => $userId,
            'source' => 'internal',
            'form1_data' => [
                'name' => 'SPPG Bandung Kidul',
                'address' => 'Jl. Terusan Buah Batu No. 5',
                'district' => 'Bandung Kidul',
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'capacity' => 12,
            ],
            'form2_data' => [
                'name' => 'Fahri Admin',
                'email' => 'fahri@sppg.test',
                'password' => 'password123',
            ],
            'form3_data' => null,
            'latitude' => -6.9600,
            'longitude' => 107.6320,
            'confirmed_latitude' => -6.9605,
            'confirmed_longitude' => 107.6315,
            'point_status' => 'green',
            'map_confirmed' => true,
            'status' => 'registered',
            'submitted_at' => now()->subDay(),
        ]);

        $draft3->partners()->createMany([
            [
                'school_name' => 'SMK Negeri 4 Bandung',
                'npsn' => '20206004',
                'level' => 'SMK',
                'school_status' => 'public',
                'address' => 'Jl. Kliningan No.6',
                'city' => 'Bandung',
                'district' => 'Lengkong',
                'latitude' => -6.9580,
                'longitude' => 107.6230,
                'jumlah_porsi' => 1100,
                'data_source' => 'database',
            ],
        ]);

        $this->command->info('SPPGSubmission seeder selesai: 3 drafts seeded.');
    }
}
