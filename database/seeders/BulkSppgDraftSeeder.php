<?php

namespace Database\Seeders;

use App\Models\SppgDraft;
use App\Models\SppgDraftPartner;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * BulkSppgDraftSeeder
 * 150 pengajuan SPPG untuk testing validasi peta:
 *   - 50 status HIJAU  : titik valid, tidak konflik
 *   - 50 status KUNING : titik 3-5 km dari SPPG aktif (overlap ringan)
 *   - 50 status MERAH  : titik 0.3-2.5 km dari SPPG aktif (konflik langsung)
 *
 * Submission number pakai prefix BULK- agar tidak bentrok dengan data existing.
 */
class BulkSppgDraftSeeder extends Seeder
{
    public function run(): void
    {
        SppgDraftPartner::query()->delete();
        SppgDraft::whereRaw("submission_number LIKE 'BULK-%'")->delete();

        $superadmin = User::where('role_type', 'super_admin')->first();
        $userId     = $superadmin ? $superadmin->id : 1;

        $created = 0;

        // ── 50 HIJAU ──────────────────────────────────────────────
        // GREEN | BULK-20260607-001
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-001',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Sukajadi", "address": "Jl. Raya Sukajadi No.2", "district": "Sukajadi", "city": "Bandung", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin 01", "email": "admin.01@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 01", "email": "gizi.01@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 01", "email": "logistik.01@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.877321,
            'longitude'           => 107.594214,
            'confirmed_latitude'  => -6.877276,
            'confirmed_longitude' => 107.593265,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MI Negeri 9 Bojongloa Kaler', 'npsn' => '20306069', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Pajajaran No.144, Bojongloa Kaler', 'city' => 'Kota Bandung', 'district' => 'Bojongloa Kaler', 'latitude' => -6.881981, 'longitude' => 107.596672, 'jumlah_porsi' => 173, 'data_source' => 'database'],
            ['school_name' => 'SD Negeri 39 Cicendo', 'npsn' => '20300006', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jl. Rancabolang No.180, Cicendo', 'city' => 'Kota Bandung', 'district' => 'Cicendo', 'latitude' => -6.882571, 'longitude' => 107.594779, 'jumlah_porsi' => 384, 'data_source' => 'database'],
            ['school_name' => 'SD Sukajadi 2', 'npsn' => '20300740', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jl. Raya No.19, Sukajadi', 'city' => 'Kota Bandung', 'district' => 'Sukajadi', 'latitude' => -6.873765, 'longitude' => 107.585982, 'jumlah_porsi' => 169, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-002
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-002',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Buah Batu", "address": "Jl. Raya Buah Batu No.37", "district": "Buah Batu", "city": "Bandung", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin 02", "email": "admin.02@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 02", "email": "gizi.02@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 02", "email": "logistik.02@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.954469,
            'longitude'           => 107.615566,
            'confirmed_latitude'  => -6.954102,
            'confirmed_longitude' => 107.614859,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SD Margahayu 37', 'npsn' => '20310750', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jalan Asia Afrika No.168, Margahayu', 'city' => 'Kabupaten Bandung', 'district' => 'Margahayu', 'latitude' => -6.956029, 'longitude' => 107.621154, 'jumlah_porsi' => 600, 'data_source' => 'database'],
            ['school_name' => 'MA Negeri 43 Dayeuhkolot', 'npsn' => '20310835', 'level' => 'MA', 'school_status' => 'public', 'address' => 'Jl. Setiabudi No.36, Dayeuhkolot', 'city' => 'Kabupaten Bandung', 'district' => 'Dayeuhkolot', 'latitude' => -6.961484, 'longitude' => 107.615374, 'jumlah_porsi' => 254, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-003
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-003',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Gedebage", "address": "Jl. Raya Gedebage No.5", "district": "Gedebage", "city": "Bandung", "province": "Jawa Barat", "capacity": 20}', true),
            'form2_data'          => json_decode('{"name": "Admin 03", "email": "admin.03@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 03", "email": "gizi.03@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 03", "email": "logistik.03@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.933094,
            'longitude'           => 107.677951,
            'confirmed_latitude'  => -6.933377,
            'confirmed_longitude' => 107.677856,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Cibiru 27', 'npsn' => '20304352', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Pasirkaliki No.68, Cibiru', 'city' => 'Kota Bandung', 'district' => 'Cibiru', 'latitude' => -6.928726, 'longitude' => 107.685493, 'jumlah_porsi' => 364, 'data_source' => 'database'],
            ['school_name' => 'SMK Ujungberung 50', 'npsn' => '20304762', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jalan Pemuda No.163, Ujungberung', 'city' => 'Kota Bandung', 'district' => 'Ujungberung', 'latitude' => -6.93675, 'longitude' => 107.661421, 'jumlah_porsi' => 480, 'data_source' => 'database'],
            ['school_name' => 'MTs Al-Falah Ujungberung', 'npsn' => '20304145', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jalan Pemuda No.117, Ujungberung', 'city' => 'Kota Bandung', 'district' => 'Ujungberung', 'latitude' => -6.917229, 'longitude' => 107.668661, 'jumlah_porsi' => 620, 'data_source' => 'database'],
            ['school_name' => 'MTs Al-Falah Arcamanik', 'npsn' => '20304541', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jalan Cibadak No.171, Arcamanik', 'city' => 'Kota Bandung', 'district' => 'Arcamanik', 'latitude' => -6.946519, 'longitude' => 107.691587, 'jumlah_porsi' => 394, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-004
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-004',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Coblong", "address": "Jl. Raya Coblong No.90", "district": "Coblong", "city": "Bandung", "province": "Jawa Barat", "capacity": 11}', true),
            'form2_data'          => json_decode('{"name": "Admin 04", "email": "admin.04@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 04", "email": "gizi.04@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 04", "email": "logistik.04@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.895576,
            'longitude'           => 107.612613,
            'confirmed_latitude'  => -6.896001,
            'confirmed_longitude' => 107.612942,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MTs Negeri 42 Sukasari', 'npsn' => '20300417', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Cibadak No.36, Sukasari', 'city' => 'Kota Bandung', 'district' => 'Sukasari', 'latitude' => -6.898696, 'longitude' => 107.613341, 'jumlah_porsi' => 369, 'data_source' => 'database'],
            ['school_name' => 'MA Negeri 40 Lengkong', 'npsn' => '20302198', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Veteran No.191, Lengkong', 'city' => 'Kota Bandung', 'district' => 'Lengkong', 'latitude' => -6.89046, 'longitude' => 107.612281, 'jumlah_porsi' => 706, 'data_source' => 'database'],
            ['school_name' => 'SMP Negeri 45 Sukajadi', 'npsn' => '20300229', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Asia Afrika No.187, Sukajadi', 'city' => 'Kota Bandung', 'district' => 'Sukajadi', 'latitude' => -6.898794, 'longitude' => 107.607945, 'jumlah_porsi' => 376, 'data_source' => 'database'],
            ['school_name' => 'MI Islamiyah Cicendo', 'npsn' => '20301120', 'level' => 'MI', 'school_status' => 'public', 'address' => 'Jl. Buah Batu No.21, Cicendo', 'city' => 'Kota Bandung', 'district' => 'Cicendo', 'latitude' => -6.889366, 'longitude' => 107.614556, 'jumlah_porsi' => 635, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-005
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-005',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Regol", "address": "Jl. Raya Regol No.71", "district": "Regol", "city": "Bandung", "province": "Jawa Barat", "capacity": 18}', true),
            'form2_data'          => json_decode('{"name": "Admin 05", "email": "admin.05@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 05", "email": "gizi.05@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 05", "email": "logistik.05@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.921124,
            'longitude'           => 107.614567,
            'confirmed_latitude'  => -6.922016,
            'confirmed_longitude' => 107.615054,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SD Negeri 30 Batununggal', 'npsn' => '20302116', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Diponegoro No.117, Batununggal', 'city' => 'Kota Bandung', 'district' => 'Batununggal', 'latitude' => -6.92982, 'longitude' => 107.613075, 'jumlah_porsi' => 643, 'data_source' => 'database'],
            ['school_name' => 'MTs Al-Falah Lengkong', 'npsn' => '20301778', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jalan Asia Afrika No.33, Lengkong', 'city' => 'Kota Bandung', 'district' => 'Lengkong', 'latitude' => -6.926657, 'longitude' => 107.62398, 'jumlah_porsi' => 488, 'data_source' => 'database'],
            ['school_name' => 'MI Al-Hikmah 15', 'npsn' => '20302293', 'level' => 'MI', 'school_status' => 'public', 'address' => 'Jl. Pelajar No.17, Bandung Wetan', 'city' => 'Kota Bandung', 'district' => 'Bandung Wetan', 'latitude' => -6.914602, 'longitude' => 107.605792, 'jumlah_porsi' => 577, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-006
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-006',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Andir", "address": "Jl. Raya Andir No.82", "district": "Andir", "city": "Bandung", "province": "Jawa Barat", "capacity": 15}', true),
            'form2_data'          => json_decode('{"name": "Admin 06", "email": "admin.06@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 06", "email": "gizi.06@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 06", "email": "logistik.06@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.908794,
            'longitude'           => 107.592809,
            'confirmed_latitude'  => -6.908385,
            'confirmed_longitude' => 107.593104,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMKN 34 Bandung Kulon', 'npsn' => '20305595', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Pajajaran No.155, Bandung Kulon', 'city' => 'Kota Bandung', 'district' => 'Bandung Kulon', 'latitude' => -6.903773, 'longitude' => 107.595959, 'jumlah_porsi' => 434, 'data_source' => 'database'],
            ['school_name' => 'SMA Sukasari 24', 'npsn' => '20300437', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Pajajaran No.127, Sukasari', 'city' => 'Kota Bandung', 'district' => 'Sukasari', 'latitude' => -6.907113, 'longitude' => 107.585342, 'jumlah_porsi' => 424, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-007
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-007',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Bojongloa Kidul", "address": "Jl. Raya Bojongloa Kidul No.23", "district": "Bojongloa Kidul", "city": "Bandung", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin 07", "email": "admin.07@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 07", "email": "gizi.07@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 07", "email": "logistik.07@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.942239,
            'longitude'           => 107.599643,
            'confirmed_latitude'  => -6.941272,
            'confirmed_longitude' => 107.598792,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MTs Al-Falah Buah Batu', 'npsn' => '20303316', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jl. Kebon Jati No.162, Buah Batu', 'city' => 'Kota Bandung', 'district' => 'Buah Batu', 'latitude' => -6.939922, 'longitude' => 107.601035, 'jumlah_porsi' => 676, 'data_source' => 'database'],
            ['school_name' => 'SMK Negeri 7 Rancasari', 'npsn' => '20303277', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Pasirkaliki No.123, Rancasari', 'city' => 'Kota Bandung', 'district' => 'Rancasari', 'latitude' => -6.953218, 'longitude' => 107.602812, 'jumlah_porsi' => 305, 'data_source' => 'database'],
            ['school_name' => 'SD Negeri 29 Margacinta', 'npsn' => '20303526', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jalan Gatot Subroto No.180, Margacinta', 'city' => 'Kota Bandung', 'district' => 'Margacinta', 'latitude' => -6.95527, 'longitude' => 107.607598, 'jumlah_porsi' => 652, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-008
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-008',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Batununggal", "address": "Jl. Raya Batununggal No.36", "district": "Batununggal", "city": "Bandung", "province": "Jawa Barat", "capacity": 8}', true),
            'form2_data'          => json_decode('{"name": "Admin 08", "email": "admin.08@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 08", "email": "gizi.08@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 08", "email": "logistik.08@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.887649,
            'longitude'           => 107.636194,
            'confirmed_latitude'  => -6.887536,
            'confirmed_longitude' => 107.635454,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Sumur Bandung 5', 'npsn' => '20301159', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Pahlawan No.90, Sumur Bandung', 'city' => 'Kota Bandung', 'district' => 'Sumur Bandung', 'latitude' => -6.889413, 'longitude' => 107.635651, 'jumlah_porsi' => 956, 'data_source' => 'database'],
            ['school_name' => 'SMK Teknologi Coblong', 'npsn' => '20300126', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Setiabudhi No.21, Coblong', 'city' => 'Kota Bandung', 'district' => 'Coblong', 'latitude' => -6.882187, 'longitude' => 107.627935, 'jumlah_porsi' => 940, 'data_source' => 'database'],
            ['school_name' => 'MI Al-Hikmah 32', 'npsn' => '20302044', 'level' => 'MI', 'school_status' => 'public', 'address' => 'Jl. Pahlawan No.168, Lengkong', 'city' => 'Kota Bandung', 'district' => 'Lengkong', 'latitude' => -6.899858, 'longitude' => 107.63638, 'jumlah_porsi' => 209, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-009
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-009',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Margacinta", "address": "Jl. Raya Margacinta No.20", "district": "Margacinta", "city": "Bandung", "province": "Jawa Barat", "capacity": 10}', true),
            'form2_data'          => json_decode('{"name": "Admin 09", "email": "admin.09@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 09", "email": "gizi.09@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 09", "email": "logistik.09@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.964944,
            'longitude'           => 107.644663,
            'confirmed_latitude'  => -6.964087,
            'confirmed_longitude' => 107.644857,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Negeri 25 Bojongsoang', 'npsn' => '20311303', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jalan Setiabudi No.100, Bojongsoang', 'city' => 'Kabupaten Bandung', 'district' => 'Bojongsoang', 'latitude' => -6.96491, 'longitude' => 107.642244, 'jumlah_porsi' => 1345, 'data_source' => 'database'],
            ['school_name' => 'SMKN 12 Margacinta', 'npsn' => '20303244', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Asia Afrika No.33, Margacinta', 'city' => 'Kota Bandung', 'district' => 'Margacinta', 'latitude' => -6.962199, 'longitude' => 107.645827, 'jumlah_porsi' => 1333, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-010
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-010',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Arcamanik", "address": "Jl. Raya Arcamanik No.91", "district": "Arcamanik", "city": "Bandung", "province": "Jawa Barat", "capacity": 13}', true),
            'form2_data'          => json_decode('{"name": "Admin 10", "email": "admin.10@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 10", "email": "gizi.10@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 10", "email": "logistik.10@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.925094,
            'longitude'           => 107.660727,
            'confirmed_latitude'  => -6.925458,
            'confirmed_longitude' => 107.660167,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MA Negeri 32 Cibiru', 'npsn' => '20304309', 'level' => 'MA', 'school_status' => 'private', 'address' => 'Jl. Pajajaran No.152, Cibiru', 'city' => 'Kota Bandung', 'district' => 'Cibiru', 'latitude' => -6.929081, 'longitude' => 107.65324, 'jumlah_porsi' => 550, 'data_source' => 'database'],
            ['school_name' => 'MTs Al-Falah Ujungberung', 'npsn' => '20304145', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jalan Pemuda No.117, Ujungberung', 'city' => 'Kota Bandung', 'district' => 'Ujungberung', 'latitude' => -6.917229, 'longitude' => 107.668661, 'jumlah_porsi' => 620, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-011
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-011',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Cimahi Utara", "address": "Jl. Raya Cimahi Utara No.70", "district": "Cimahi Utara", "city": "Cimahi", "province": "Jawa Barat", "capacity": 10}', true),
            'form2_data'          => json_decode('{"name": "Admin 11", "email": "admin.11@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 11", "email": "gizi.11@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 11", "email": "logistik.11@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.869794,
            'longitude'           => 107.537863,
            'confirmed_latitude'  => -6.86972,
            'confirmed_longitude' => 107.537047,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMPN 4 Cimahi Selatan', 'npsn' => '20306675', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Ahmad Yani No.25, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.871867, 'longitude' => 107.533824, 'jumlah_porsi' => 358, 'data_source' => 'database'],
            ['school_name' => 'SMAN 8 Cimahi Selatan', 'npsn' => '20306952', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Veteran No.70, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.87774, 'longitude' => 107.540727, 'jumlah_porsi' => 1095, 'data_source' => 'database'],
            ['school_name' => 'MA Assalam 24', 'npsn' => '20307563', 'level' => 'MA', 'school_status' => 'public', 'address' => 'Jalan Sudirman No.187, Cimahi Tengah', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Tengah', 'latitude' => -6.879081, 'longitude' => 107.536222, 'jumlah_porsi' => 284, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-012
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-012',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Cimahi Tengah", "address": "Jl. Raya Cimahi Tengah No.38", "district": "Cimahi Tengah", "city": "Cimahi", "province": "Jawa Barat", "capacity": 13}', true),
            'form2_data'          => json_decode('{"name": "Admin 12", "email": "admin.12@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 12", "email": "gizi.12@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 12", "email": "logistik.12@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.881166,
            'longitude'           => 107.541633,
            'confirmed_latitude'  => -6.881729,
            'confirmed_longitude' => 107.542138,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMP Negeri 8 Cimahi Utara', 'npsn' => '20307805', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Pasirkaliki No.133, Cimahi Utara', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Utara', 'latitude' => -6.881109, 'longitude' => 107.543358, 'jumlah_porsi' => 368, 'data_source' => 'database'],
            ['school_name' => 'SMAN 8 Cimahi Selatan', 'npsn' => '20306952', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Veteran No.70, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.87774, 'longitude' => 107.540727, 'jumlah_porsi' => 1095, 'data_source' => 'database'],
            ['school_name' => 'MA Assalam 24', 'npsn' => '20307563', 'level' => 'MA', 'school_status' => 'public', 'address' => 'Jalan Sudirman No.187, Cimahi Tengah', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Tengah', 'latitude' => -6.879081, 'longitude' => 107.536222, 'jumlah_porsi' => 284, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-013
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-013',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Cimahi Selatan", "address": "Jl. Raya Cimahi Selatan No.15", "district": "Cimahi Selatan", "city": "Cimahi", "province": "Jawa Barat", "capacity": 18}', true),
            'form2_data'          => json_decode('{"name": "Admin 13", "email": "admin.13@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 13", "email": "gizi.13@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 13", "email": "logistik.13@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.900034,
            'longitude'           => 107.538237,
            'confirmed_latitude'  => -6.899696,
            'confirmed_longitude' => 107.538128,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SD Negeri 24 Cimahi Selatan', 'npsn' => '20307304', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Setiabudhi No.121, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.899674, 'longitude' => 107.533841, 'jumlah_porsi' => 482, 'data_source' => 'database'],
            ['school_name' => 'SMA Cimahi Selatan 37', 'npsn' => '20307271', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Gatot Subroto No.110, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.901942, 'longitude' => 107.531986, 'jumlah_porsi' => 1368, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-014
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-014',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Padalarang", "address": "Jl. Raya Padalarang No.15", "district": "Padalarang", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 13}', true),
            'form2_data'          => json_decode('{"name": "Admin 14", "email": "admin.14@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 14", "email": "gizi.14@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 14", "email": "logistik.14@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.830411,
            'longitude'           => 107.466826,
            'confirmed_latitude'  => -6.830639,
            'confirmed_longitude' => 107.466906,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMKN 9 Padalarang', 'npsn' => '20308115', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Cihampelas No.120, Padalarang', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Padalarang', 'latitude' => -6.830264, 'longitude' => 107.461535, 'jumlah_porsi' => 1329, 'data_source' => 'database'],
            ['school_name' => 'SMK Teknologi Cipatat', 'npsn' => '20307817', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Diponegoro No.35, Cipatat', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Cipatat', 'latitude' => -6.820928, 'longitude' => 107.467389, 'jumlah_porsi' => 1111, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 10 Cikalongwetan', 'npsn' => '20308389', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Gatot Subroto No.99, Cikalongwetan', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Cikalongwetan', 'latitude' => -6.820315, 'longitude' => 107.465543, 'jumlah_porsi' => 527, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-015
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-015',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Lembang", "address": "Jl. Raya Lembang No.66", "district": "Lembang", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 20}', true),
            'form2_data'          => json_decode('{"name": "Admin 15", "email": "admin.15@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 15", "email": "gizi.15@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 15", "email": "logistik.15@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.814753,
            'longitude'           => 107.615169,
            'confirmed_latitude'  => -6.814147,
            'confirmed_longitude' => 107.61506,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMP Negeri 37 Lembang', 'npsn' => '20309701', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Asia Afrika No.130, Lembang', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Lembang', 'latitude' => -6.8261, 'longitude' => 107.611996, 'jumlah_porsi' => 268, 'data_source' => 'database'],
            ['school_name' => 'MTs Persatuan 36', 'npsn' => '20310317', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jl. Setiabudhi No.63, Ngamprah', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Ngamprah', 'latitude' => -6.823243, 'longitude' => 107.60076, 'jumlah_porsi' => 180, 'data_source' => 'database'],
            ['school_name' => 'SMKN 4 Parongpong', 'npsn' => '20310415', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Gedebage No.155, Parongpong', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Parongpong', 'latitude' => -6.804437, 'longitude' => 107.630339, 'jumlah_porsi' => 585, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-016
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-016',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Batujajar", "address": "Jl. Raya Batujajar No.11", "district": "Batujajar", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 13}', true),
            'form2_data'          => json_decode('{"name": "Admin 16", "email": "admin.16@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 16", "email": "gizi.16@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 16", "email": "logistik.16@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.822036,
            'longitude'           => 107.511515,
            'confirmed_latitude'  => -6.82114,
            'confirmed_longitude' => 107.51085,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Negeri 19 Cipatat', 'npsn' => '20309006', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jalan Setiabudhi No.89, Cipatat', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Cipatat', 'latitude' => -6.815427, 'longitude' => 107.486584, 'jumlah_porsi' => 794, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 40 Cikalongwetan', 'npsn' => '20308033', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Gatot Subroto No.102, Cikalongwetan', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Cikalongwetan', 'latitude' => -6.812995, 'longitude' => 107.481916, 'jumlah_porsi' => 1276, 'data_source' => 'database'],
            ['school_name' => 'SMPN 35 Batujajar', 'npsn' => '20308959', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jalan Gedebage No.198, Batujajar', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Batujajar', 'latitude' => -6.820127, 'longitude' => 107.47984, 'jumlah_porsi' => 370, 'data_source' => 'database'],
            ['school_name' => 'SMK Negeri 40 Batujajar', 'npsn' => '20308238', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jalan Rancabolang No.140, Batujajar', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Batujajar', 'latitude' => -6.851369, 'longitude' => 107.494861, 'jumlah_porsi' => 1189, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-017
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-017',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Ngamprah", "address": "Jl. Raya Ngamprah No.88", "district": "Ngamprah", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 18}', true),
            'form2_data'          => json_decode('{"name": "Admin 17", "email": "admin.17@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 17", "email": "gizi.17@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 17", "email": "logistik.17@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.853731,
            'longitude'           => 107.547108,
            'confirmed_latitude'  => -6.853883,
            'confirmed_longitude' => 107.546316,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MA Assalam 7', 'npsn' => '20306753', 'level' => 'MA', 'school_status' => 'public', 'address' => 'Jl. Gedebage No.87, Cimahi Tengah', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Tengah', 'latitude' => -6.854654, 'longitude' => 107.546426, 'jumlah_porsi' => 485, 'data_source' => 'database'],
            ['school_name' => 'SMPN 22 Cimahi Selatan', 'npsn' => '20307493', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Soekarno-Hatta No.112, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.853947, 'longitude' => 107.553247, 'jumlah_porsi' => 495, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-018
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-018',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Parongpong", "address": "Jl. Raya Parongpong No.20", "district": "Parongpong", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 19}', true),
            'form2_data'          => json_decode('{"name": "Admin 18", "email": "admin.18@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 18", "email": "gizi.18@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 18", "email": "logistik.18@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.788534,
            'longitude'           => 107.584398,
            'confirmed_latitude'  => -6.789486,
            'confirmed_longitude' => 107.584832,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SD Negeri 35 Parongpong', 'npsn' => '20309256', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.46, Parongpong', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Parongpong', 'latitude' => -6.786545, 'longitude' => 107.584759, 'jumlah_porsi' => 506, 'data_source' => 'database'],
            ['school_name' => 'SMK Parongpong 35', 'npsn' => '20310059', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Pahlawan No.129, Parongpong', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Parongpong', 'latitude' => -6.783325, 'longitude' => 107.595528, 'jumlah_porsi' => 1379, 'data_source' => 'database'],
            ['school_name' => 'SMPN 29 Cisarua', 'npsn' => '20309096', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jalan Pelajar No.52, Cisarua', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Cisarua', 'latitude' => -6.792273, 'longitude' => 107.597541, 'jumlah_porsi' => 675, 'data_source' => 'database'],
            ['school_name' => 'SMK Teknologi Parongpong', 'npsn' => '20309192', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Rancabolang No.52, Parongpong', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Parongpong', 'latitude' => -6.796167, 'longitude' => 107.598418, 'jumlah_porsi' => 784, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-019
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-019',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Dayeuhkolot", "address": "Jl. Raya Dayeuhkolot No.72", "district": "Dayeuhkolot", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 20}', true),
            'form2_data'          => json_decode('{"name": "Admin 19", "email": "admin.19@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 19", "email": "gizi.19@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 19", "email": "logistik.19@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.984125,
            'longitude'           => 107.630543,
            'confirmed_latitude'  => -6.984984,
            'confirmed_longitude' => 107.631186,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Teknologi Dayeuhkolot', 'npsn' => '20311035', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Setiabudhi No.142, Dayeuhkolot', 'city' => 'Kabupaten Bandung', 'district' => 'Dayeuhkolot', 'latitude' => -6.981297, 'longitude' => 107.637447, 'jumlah_porsi' => 550, 'data_source' => 'database'],
            ['school_name' => 'MA Al-Azhar Baleendah', 'npsn' => '20311507', 'level' => 'MA', 'school_status' => 'public', 'address' => 'Jl. Setiabudi No.173, Baleendah', 'city' => 'Kabupaten Bandung', 'district' => 'Baleendah', 'latitude' => -6.980038, 'longitude' => 107.637261, 'jumlah_porsi' => 347, 'data_source' => 'database'],
            ['school_name' => 'MI Negeri 40 Dayeuhkolot', 'npsn' => '20310903', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jalan Raya No.2, Dayeuhkolot', 'city' => 'Kabupaten Bandung', 'district' => 'Dayeuhkolot', 'latitude' => -6.978259, 'longitude' => 107.625172, 'jumlah_porsi' => 117, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-020
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-020',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Baleendah", "address": "Jl. Raya Baleendah No.41", "district": "Baleendah", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 14}', true),
            'form2_data'          => json_decode('{"name": "Admin 20", "email": "admin.20@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 20", "email": "gizi.20@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 20", "email": "logistik.20@sppg.test", "password": "password123"}}', true),
            'latitude'            => -7.010924,
            'longitude'           => 107.619511,
            'confirmed_latitude'  => -7.01189,
            'confirmed_longitude' => 107.619858,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Baleendah 31', 'npsn' => '20310511', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Gatot Subroto No.4, Baleendah', 'city' => 'Kabupaten Bandung', 'district' => 'Baleendah', 'latitude' => -6.995578, 'longitude' => 107.613119, 'jumlah_porsi' => 440, 'data_source' => 'database'],
            ['school_name' => 'SMKN 6 Margahayu', 'npsn' => '20311024', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Cihampelas No.145, Margahayu', 'city' => 'Kabupaten Bandung', 'district' => 'Margahayu', 'latitude' => -6.993881, 'longitude' => 107.640741, 'jumlah_porsi' => 814, 'data_source' => 'database'],
            ['school_name' => 'SMPN 31 Baleendah', 'npsn' => '20311470', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Setiabudhi No.82, Baleendah', 'city' => 'Kabupaten Bandung', 'district' => 'Baleendah', 'latitude' => -6.991273, 'longitude' => 107.640617, 'jumlah_porsi' => 602, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-021
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-021',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Soreang", "address": "Jl. Raya Soreang No.75", "district": "Soreang", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 20}', true),
            'form2_data'          => json_decode('{"name": "Admin 21", "email": "admin.21@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 21", "email": "gizi.21@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 21", "email": "logistik.21@sppg.test", "password": "password123"}}', true),
            'latitude'            => -7.033586,
            'longitude'           => 107.521183,
            'confirmed_latitude'  => -7.034495,
            'confirmed_longitude' => 107.52188,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Katapang 34', 'npsn' => '20311997', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Ahmad Yani No.144, Katapang', 'city' => 'Kabupaten Bandung', 'district' => 'Katapang', 'latitude' => -7.03398, 'longitude' => 107.517933, 'jumlah_porsi' => 1029, 'data_source' => 'database'],
            ['school_name' => 'SMA Soreang 32', 'npsn' => '20312337', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Setiabudhi No.144, Soreang', 'city' => 'Kabupaten Bandung', 'district' => 'Soreang', 'latitude' => -7.029706, 'longitude' => 107.519726, 'jumlah_porsi' => 1207, 'data_source' => 'database'],
            ['school_name' => 'SMK Negeri 6 Katapang', 'npsn' => '20312396', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Gatot Subroto No.9, Katapang', 'city' => 'Kabupaten Bandung', 'district' => 'Katapang', 'latitude' => -7.04217, 'longitude' => 107.522048, 'jumlah_porsi' => 544, 'data_source' => 'database'],
            ['school_name' => 'SMK Kutawaringin 43', 'npsn' => '20312964', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Setiabudhi No.161, Kutawaringin', 'city' => 'Kabupaten Bandung', 'district' => 'Kutawaringin', 'latitude' => -7.040513, 'longitude' => 107.530862, 'jumlah_porsi' => 679, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-022
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-022',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Katapang", "address": "Jl. Raya Katapang No.39", "district": "Katapang", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 20}', true),
            'form2_data'          => json_decode('{"name": "Admin 22", "email": "admin.22@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 22", "email": "gizi.22@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 22", "email": "logistik.22@sppg.test", "password": "password123"}}', true),
            'latitude'            => -7.00572,
            'longitude'           => 107.556061,
            'confirmed_latitude'  => -7.005507,
            'confirmed_longitude' => 107.555624,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Negeri 44 Banjaran', 'npsn' => '20312701', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.16, Banjaran', 'city' => 'Kabupaten Bandung', 'district' => 'Banjaran', 'latitude' => -7.003379, 'longitude' => 107.539991, 'jumlah_porsi' => 459, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 36 Cangkuang', 'npsn' => '20312493', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Cibadak No.199, Cangkuang', 'city' => 'Kabupaten Bandung', 'district' => 'Cangkuang', 'latitude' => -7.02767, 'longitude' => 107.546736, 'jumlah_porsi' => 1310, 'data_source' => 'database'],
            ['school_name' => 'SMPN 32 Banjaran', 'npsn' => '20312897', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Merdeka No.26, Banjaran', 'city' => 'Kabupaten Bandung', 'district' => 'Banjaran', 'latitude' => -7.023179, 'longitude' => 107.538338, 'jumlah_porsi' => 116, 'data_source' => 'database'],
            ['school_name' => 'SMK Teknologi Cangkuang', 'npsn' => '20311577', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jalan Kebon Jati No.103, Cangkuang', 'city' => 'Kabupaten Bandung', 'district' => 'Cangkuang', 'latitude' => -7.028142, 'longitude' => 107.543065, 'jumlah_porsi' => 1056, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-023
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-023',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Margaasih", "address": "Jl. Raya Margaasih No.21", "district": "Margaasih", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 8}', true),
            'form2_data'          => json_decode('{"name": "Admin 23", "email": "admin.23@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 23", "email": "gizi.23@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 23", "email": "logistik.23@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.971186,
            'longitude'           => 107.593221,
            'confirmed_latitude'  => -6.971119,
            'confirmed_longitude' => 107.592494,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Negeri 49 Rancasari', 'npsn' => '20303796', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Sudirman No.81, Rancasari', 'city' => 'Kota Bandung', 'district' => 'Rancasari', 'latitude' => -6.963384, 'longitude' => 107.599085, 'jumlah_porsi' => 1027, 'data_source' => 'database'],
            ['school_name' => 'SMA Buah Batu 49', 'npsn' => '20303366', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Cibadak No.170, Buah Batu', 'city' => 'Kota Bandung', 'district' => 'Buah Batu', 'latitude' => -6.972639, 'longitude' => 107.605835, 'jumlah_porsi' => 1330, 'data_source' => 'database'],
            ['school_name' => 'SMA Margahayu 11', 'npsn' => '20311339', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jalan Gatot Subroto No.69, Margahayu', 'city' => 'Kabupaten Bandung', 'district' => 'Margahayu', 'latitude' => -6.980372, 'longitude' => 107.606217, 'jumlah_porsi' => 1090, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-024
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-024',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Banjaran", "address": "Jl. Raya Banjaran No.19", "district": "Banjaran", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin 24", "email": "admin.24@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 24", "email": "gizi.24@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 24", "email": "logistik.24@sppg.test", "password": "password123"}}', true),
            'latitude'            => -7.057032,
            'longitude'           => 107.593495,
            'confirmed_latitude'  => -7.057436,
            'confirmed_longitude' => 107.594269,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMAN 4 Katapang', 'npsn' => '20312611', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Buah Batu No.138, Katapang', 'city' => 'Kabupaten Bandung', 'district' => 'Katapang', 'latitude' => -7.047926, 'longitude' => 107.546088, 'jumlah_porsi' => 799, 'data_source' => 'database'],
            ['school_name' => 'MI Negeri 14 Banjaran', 'npsn' => '20312551', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Rancabolang No.193, Banjaran', 'city' => 'Kabupaten Bandung', 'district' => 'Banjaran', 'latitude' => -7.043647, 'longitude' => 107.540413, 'jumlah_porsi' => 133, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-025
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-025',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Bojongsoang", "address": "Jl. Raya Bojongsoang No.16", "district": "Bojongsoang", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 16}', true),
            'form2_data'          => json_decode('{"name": "Admin 25", "email": "admin.25@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 25", "email": "gizi.25@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 25", "email": "logistik.25@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.990426,
            'longitude'           => 107.670112,
            'confirmed_latitude'  => -6.991342,
            'confirmed_longitude' => 107.670257,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MI Negeri 22 Dayeuhkolot', 'npsn' => '20311143', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jalan Raya No.163, Dayeuhkolot', 'city' => 'Kabupaten Bandung', 'district' => 'Dayeuhkolot', 'latitude' => -6.992253, 'longitude' => 107.656825, 'jumlah_porsi' => 602, 'data_source' => 'database'],
            ['school_name' => 'MI Negeri 48 Baleendah', 'npsn' => '20311225', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jalan Soekarno-Hatta No.28, Baleendah', 'city' => 'Kabupaten Bandung', 'district' => 'Baleendah', 'latitude' => -7.00208, 'longitude' => 107.654123, 'jumlah_porsi' => 555, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-026
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-026',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Sumedang Utara", "address": "Jl. Raya Sumedang Utara No.96", "district": "Sumedang Utara", "city": "Kabupaten Sumedang", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin 26", "email": "admin.26@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 26", "email": "gizi.26@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 26", "email": "logistik.26@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.847628,
            'longitude'           => 107.92247,
            'confirmed_latitude'  => -6.847145,
            'confirmed_longitude' => 107.922922,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMP Sumedang Selatan 25', 'npsn' => '20313037', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Sudirman No.136, Sumedang Selatan', 'city' => 'Kabupaten Sumedang', 'district' => 'Sumedang Selatan', 'latitude' => -6.846579, 'longitude' => 107.926006, 'jumlah_porsi' => 268, 'data_source' => 'database'],
            ['school_name' => 'SMK Negeri 10 Sumedang Selatan', 'npsn' => '20313926', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jalan Raya No.60, Sumedang Selatan', 'city' => 'Kabupaten Sumedang', 'district' => 'Sumedang Selatan', 'latitude' => -6.847754, 'longitude' => 107.927401, 'jumlah_porsi' => 219, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-027
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-027',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Jatinangor", "address": "Jl. Raya Jatinangor No.21", "district": "Jatinangor", "city": "Kabupaten Sumedang", "province": "Jawa Barat", "capacity": 16}', true),
            'form2_data'          => json_decode('{"name": "Admin 27", "email": "admin.27@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 27", "email": "gizi.27@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 27", "email": "logistik.27@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.870873,
            'longitude'           => 107.891459,
            'confirmed_latitude'  => -6.871505,
            'confirmed_longitude' => 107.891693,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MTs Negeri 34 Sumedang Selatan', 'npsn' => '20314100', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jalan Cihampelas No.192, Sumedang Selatan', 'city' => 'Kabupaten Sumedang', 'district' => 'Sumedang Selatan', 'latitude' => -6.872438, 'longitude' => 107.897982, 'jumlah_porsi' => 236, 'data_source' => 'database'],
            ['school_name' => 'MTs Negeri 27 Sumedang Selatan', 'npsn' => '20313082', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Sudirman No.26, Sumedang Selatan', 'city' => 'Kabupaten Sumedang', 'district' => 'Sumedang Selatan', 'latitude' => -6.869459, 'longitude' => 107.899484, 'jumlah_porsi' => 493, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-028
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-028',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Sumedang Selatan", "address": "Jl. Raya Sumedang Selatan No.7", "district": "Sumedang Selatan", "city": "Kabupaten Sumedang", "province": "Jawa Barat", "capacity": 20}', true),
            'form2_data'          => json_decode('{"name": "Admin 28", "email": "admin.28@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 28", "email": "gizi.28@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 28", "email": "logistik.28@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.833057,
            'longitude'           => 107.946814,
            'confirmed_latitude'  => -6.832354,
            'confirmed_longitude' => 107.947679,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Sumedang Utara 28', 'npsn' => '20314190', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Gatot Subroto No.67, Sumedang Utara', 'city' => 'Kabupaten Sumedang', 'district' => 'Sumedang Utara', 'latitude' => -6.831032, 'longitude' => 107.943195, 'jumlah_porsi' => 1004, 'data_source' => 'database'],
            ['school_name' => 'SD Sumedang Utara 12', 'npsn' => '20313784', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Setiabudhi No.18, Sumedang Utara', 'city' => 'Kabupaten Sumedang', 'district' => 'Sumedang Utara', 'latitude' => -6.829622, 'longitude' => 107.939928, 'jumlah_porsi' => 418, 'data_source' => 'database'],
            ['school_name' => 'SD Jatinangor 7', 'npsn' => '20314018', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.126, Jatinangor', 'city' => 'Kabupaten Sumedang', 'district' => 'Jatinangor', 'latitude' => -6.825071, 'longitude' => 107.945727, 'jumlah_porsi' => 245, 'data_source' => 'database'],
            ['school_name' => 'MA Al-Azhar Sumedang Selatan', 'npsn' => '20313171', 'level' => 'MA', 'school_status' => 'public', 'address' => 'Jl. Pemuda No.142, Sumedang Selatan', 'city' => 'Kabupaten Sumedang', 'district' => 'Sumedang Selatan', 'latitude' => -6.844541, 'longitude' => 107.932042, 'jumlah_porsi' => 391, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-029
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-029',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Tarogong Kidul", "address": "Jl. Raya Tarogong Kidul No.33", "district": "Tarogong Kidul", "city": "Kabupaten Garut", "province": "Jawa Barat", "capacity": 18}', true),
            'form2_data'          => json_decode('{"name": "Admin 29", "email": "admin.29@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 29", "email": "gizi.29@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 29", "email": "logistik.29@sppg.test", "password": "password123"}}', true),
            'latitude'            => -7.213553,
            'longitude'           => 107.880731,
            'confirmed_latitude'  => -7.214357,
            'confirmed_longitude' => 107.881182,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Garut Kota 50', 'npsn' => '20314529', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Diponegoro No.64, Garut Kota', 'city' => 'Kabupaten Garut', 'district' => 'Garut Kota', 'latitude' => -7.211521, 'longitude' => 107.877279, 'jumlah_porsi' => 314, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 46 Banyuresmi', 'npsn' => '20314902', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Rancabolang No.43, Banyuresmi', 'city' => 'Kabupaten Garut', 'district' => 'Banyuresmi', 'latitude' => -7.214199, 'longitude' => 107.886415, 'jumlah_porsi' => 309, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-030
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-030',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Garut Kota", "address": "Jl. Raya Garut Kota No.31", "district": "Garut Kota", "city": "Kabupaten Garut", "province": "Jawa Barat", "capacity": 18}', true),
            'form2_data'          => json_decode('{"name": "Admin 30", "email": "admin.30@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 30", "email": "gizi.30@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 30", "email": "logistik.30@sppg.test", "password": "password123"}}', true),
            'latitude'            => -7.230485,
            'longitude'           => 107.908571,
            'confirmed_latitude'  => -7.231285,
            'confirmed_longitude' => 107.90881,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MTs Nurul Islam 18', 'npsn' => '20314431', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jl. Ahmad Yani No.153, Banyuresmi', 'city' => 'Kabupaten Garut', 'district' => 'Banyuresmi', 'latitude' => -7.235188, 'longitude' => 107.911564, 'jumlah_porsi' => 387, 'data_source' => 'database'],
            ['school_name' => 'MA Negeri 23 Banyuresmi', 'npsn' => '20314605', 'level' => 'MA', 'school_status' => 'public', 'address' => 'Jl. Pemuda No.152, Banyuresmi', 'city' => 'Kabupaten Garut', 'district' => 'Banyuresmi', 'latitude' => -7.229838, 'longitude' => 107.902973, 'jumlah_porsi' => 467, 'data_source' => 'database'],
            ['school_name' => 'MTs Negeri 47 Tarogong Kaler', 'npsn' => '20314685', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jalan Rancabolang No.82, Tarogong Kaler', 'city' => 'Kabupaten Garut', 'district' => 'Tarogong Kaler', 'latitude' => -7.237611, 'longitude' => 107.91554, 'jumlah_porsi' => 465, 'data_source' => 'database'],
            ['school_name' => 'MTs Nurul Islam 10', 'npsn' => '20314378', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jl. Rancabolang No.76, Banyuresmi', 'city' => 'Kabupaten Garut', 'district' => 'Banyuresmi', 'latitude' => -7.22006, 'longitude' => 107.910856, 'jumlah_porsi' => 590, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-031
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-031',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Cianjur", "address": "Jl. Raya Cianjur No.40", "district": "Cianjur", "city": "Kabupaten Cianjur", "province": "Jawa Barat", "capacity": 17}', true),
            'form2_data'          => json_decode('{"name": "Admin 31", "email": "admin.31@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 31", "email": "gizi.31@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 31", "email": "logistik.31@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.819822,
            'longitude'           => 107.137506,
            'confirmed_latitude'  => -6.819435,
            'confirmed_longitude' => 107.136811,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Teknologi Cianjur', 'npsn' => '20315338', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.190, Cianjur', 'city' => 'Kabupaten Cianjur', 'district' => 'Cianjur', 'latitude' => -6.82084, 'longitude' => 107.140898, 'jumlah_porsi' => 965, 'data_source' => 'database'],
            ['school_name' => 'MTs Al-Falah Cianjur', 'npsn' => '20315591', 'level' => 'MTs', 'school_status' => 'private', 'address' => 'Jalan Rancabolang No.71, Cianjur', 'city' => 'Kabupaten Cianjur', 'district' => 'Cianjur', 'latitude' => -6.824119, 'longitude' => 107.133275, 'jumlah_porsi' => 457, 'data_source' => 'database'],
            ['school_name' => 'SMK Negeri 32 Cugenang', 'npsn' => '20316222', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Buah Batu No.112, Cugenang', 'city' => 'Kabupaten Cianjur', 'district' => 'Cugenang', 'latitude' => -6.812784, 'longitude' => 107.134741, 'jumlah_porsi' => 1177, 'data_source' => 'database'],
            ['school_name' => 'SD Negeri 47 Pacet', 'npsn' => '20315273', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jl. Setiabudhi No.143, Pacet', 'city' => 'Kabupaten Cianjur', 'district' => 'Pacet', 'latitude' => -6.825548, 'longitude' => 107.130534, 'jumlah_porsi' => 382, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-032
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-032',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Cipanas", "address": "Jl. Raya Cipanas No.41", "district": "Cipanas", "city": "Kabupaten Cianjur", "province": "Jawa Barat", "capacity": 10}', true),
            'form2_data'          => json_decode('{"name": "Admin 32", "email": "admin.32@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 32", "email": "gizi.32@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 32", "email": "logistik.32@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.776923,
            'longitude'           => 107.084587,
            'confirmed_latitude'  => -6.777431,
            'confirmed_longitude' => 107.08403,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MI Negeri 12 Cipanas', 'npsn' => '20315471', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jalan Soekarno-Hatta No.42, Cipanas', 'city' => 'Kabupaten Cianjur', 'district' => 'Cipanas', 'latitude' => -6.801748, 'longitude' => 107.126862, 'jumlah_porsi' => 192, 'data_source' => 'database'],
            ['school_name' => 'MI Islamiyah Cianjur', 'npsn' => '20316370', 'level' => 'MI', 'school_status' => 'public', 'address' => 'Jalan Veteran No.17, Cianjur', 'city' => 'Kabupaten Cianjur', 'district' => 'Cianjur', 'latitude' => -6.802495, 'longitude' => 107.129014, 'jumlah_porsi' => 397, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-033
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-033',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Warudoyong", "address": "Jl. Raya Warudoyong No.43", "district": "Warudoyong", "city": "Kota Sukabumi", "province": "Jawa Barat", "capacity": 18}', true),
            'form2_data'          => json_decode('{"name": "Admin 33", "email": "admin.33@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 33", "email": "gizi.33@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 33", "email": "logistik.33@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.927233,
            'longitude'           => 106.926595,
            'confirmed_latitude'  => -6.926477,
            'confirmed_longitude' => 106.926521,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMKN 43 Cikole', 'npsn' => '20316850', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.170, Cikole', 'city' => 'Kota Sukabumi', 'district' => 'Cikole', 'latitude' => -6.917749, 'longitude' => 106.929016, 'jumlah_porsi' => 988, 'data_source' => 'database'],
            ['school_name' => 'MI Negeri 8 Gunung Puyuh', 'npsn' => '20316963', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jl. Diponegoro No.104, Gunung Puyuh', 'city' => 'Kota Sukabumi', 'district' => 'Gunung Puyuh', 'latitude' => -6.934738, 'longitude' => 106.933111, 'jumlah_porsi' => 100, 'data_source' => 'database'],
            ['school_name' => 'SMA Cikole 45', 'npsn' => '20317288', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Diponegoro No.100, Cikole', 'city' => 'Kota Sukabumi', 'district' => 'Cikole', 'latitude' => -6.921485, 'longitude' => 106.935781, 'jumlah_porsi' => 859, 'data_source' => 'database'],
            ['school_name' => 'SMK Teknologi Cikole', 'npsn' => '20316828', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jalan Kebon Jati No.174, Cikole', 'city' => 'Kota Sukabumi', 'district' => 'Cikole', 'latitude' => -6.933343, 'longitude' => 106.936062, 'jumlah_porsi' => 934, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-034
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-034',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Cikole", "address": "Jl. Raya Cikole No.83", "district": "Cikole", "city": "Kota Sukabumi", "province": "Jawa Barat", "capacity": 9}', true),
            'form2_data'          => json_decode('{"name": "Admin 34", "email": "admin.34@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 34", "email": "gizi.34@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 34", "email": "logistik.34@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.942252,
            'longitude'           => 106.93987,
            'confirmed_latitude'  => -6.942039,
            'confirmed_longitude' => 106.940554,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMP Warudoyong 26', 'npsn' => '20317148', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jalan Pelajar No.184, Warudoyong', 'city' => 'Kota Sukabumi', 'district' => 'Warudoyong', 'latitude' => -6.93876, 'longitude' => 106.944002, 'jumlah_porsi' => 147, 'data_source' => 'database'],
            ['school_name' => 'SMK Teknologi Cikole', 'npsn' => '20316828', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jalan Kebon Jati No.174, Cikole', 'city' => 'Kota Sukabumi', 'district' => 'Cikole', 'latitude' => -6.933343, 'longitude' => 106.936062, 'jumlah_porsi' => 934, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-035
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-035',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Cidadap", "address": "Jl. Raya Cidadap No.10", "district": "Cidadap", "city": "Bandung", "province": "Jawa Barat", "capacity": 19}', true),
            'form2_data'          => json_decode('{"name": "Admin 35", "email": "admin.35@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 35", "email": "gizi.35@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 35", "email": "logistik.35@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.861087,
            'longitude'           => 107.616964,
            'confirmed_latitude'  => -6.860632,
            'confirmed_longitude' => 107.617748,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Negeri 39 Sukasari', 'npsn' => '20300439', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Setiabudhi No.153, Sukasari', 'city' => 'Kota Bandung', 'district' => 'Sukasari', 'latitude' => -6.858183, 'longitude' => 107.611585, 'jumlah_porsi' => 1333, 'data_source' => 'database'],
            ['school_name' => 'SMKN 41 Coblong', 'npsn' => '20300505', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Pahlawan No.96, Coblong', 'city' => 'Kota Bandung', 'district' => 'Coblong', 'latitude' => -6.87081, 'longitude' => 107.612144, 'jumlah_porsi' => 1286, 'data_source' => 'database'],
            ['school_name' => 'SMA Sukajadi 16', 'npsn' => '20300005', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Diponegoro No.174, Sukajadi', 'city' => 'Kota Bandung', 'district' => 'Sukajadi', 'latitude' => -6.871991, 'longitude' => 107.61247, 'jumlah_porsi' => 1064, 'data_source' => 'database'],
            ['school_name' => 'SMAN 24 Coblong', 'npsn' => '20300599', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Merdeka No.62, Coblong', 'city' => 'Kota Bandung', 'district' => 'Coblong', 'latitude' => -6.864567, 'longitude' => 107.634335, 'jumlah_porsi' => 375, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-036
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-036',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Cicendo", "address": "Jl. Raya Cicendo No.28", "district": "Cicendo", "city": "Bandung", "province": "Jawa Barat", "capacity": 20}', true),
            'form2_data'          => json_decode('{"name": "Admin 36", "email": "admin.36@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 36", "email": "gizi.36@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 36", "email": "logistik.36@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.878874,
            'longitude'           => 107.575789,
            'confirmed_latitude'  => -6.878061,
            'confirmed_longitude' => 107.575892,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Teknologi Bojongloa Kaler', 'npsn' => '20305636', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.87, Bojongloa Kaler', 'city' => 'Kota Bandung', 'district' => 'Bojongloa Kaler', 'latitude' => -6.882302, 'longitude' => 107.574486, 'jumlah_porsi' => 394, 'data_source' => 'database'],
            ['school_name' => 'MTs Persatuan 21', 'npsn' => '20306532', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jalan Pajajaran No.39, Bojongloa Kaler', 'city' => 'Kota Bandung', 'district' => 'Bojongloa Kaler', 'latitude' => -6.875258, 'longitude' => 107.577306, 'jumlah_porsi' => 451, 'data_source' => 'database'],
            ['school_name' => 'SMA Bojongloa Kidul 4', 'npsn' => '20305388', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jalan Pajajaran No.30, Bojongloa Kidul', 'city' => 'Kota Bandung', 'district' => 'Bojongloa Kidul', 'latitude' => -6.886721, 'longitude' => 107.578707, 'jumlah_porsi' => 941, 'data_source' => 'database'],
            ['school_name' => 'MTs Negeri 15 Bandung Kulon', 'npsn' => '20305853', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Buah Batu No.169, Bandung Kulon', 'city' => 'Kota Bandung', 'district' => 'Bandung Kulon', 'latitude' => -6.882191, 'longitude' => 107.567338, 'jumlah_porsi' => 284, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-037
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-037',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Lengkong", "address": "Jl. Raya Lengkong No.99", "district": "Lengkong", "city": "Bandung", "province": "Jawa Barat", "capacity": 14}', true),
            'form2_data'          => json_decode('{"name": "Admin 37", "email": "admin.37@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 37", "email": "gizi.37@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 37", "email": "logistik.37@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.913461,
            'longitude'           => 107.631486,
            'confirmed_latitude'  => -6.913282,
            'confirmed_longitude' => 107.6322,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MI Al-Hikmah 25', 'npsn' => '20302555', 'level' => 'MI', 'school_status' => 'public', 'address' => 'Jl. Cihampelas No.84, Regol', 'city' => 'Kota Bandung', 'district' => 'Regol', 'latitude' => -6.918784, 'longitude' => 107.635671, 'jumlah_porsi' => 384, 'data_source' => 'database'],
            ['school_name' => 'MI Islamiyah Cidadap', 'npsn' => '20300814', 'level' => 'MI', 'school_status' => 'public', 'address' => 'Jalan Pelajar No.139, Cidadap', 'city' => 'Kota Bandung', 'district' => 'Cidadap', 'latitude' => -6.904875, 'longitude' => 107.633712, 'jumlah_porsi' => 684, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-038
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-038',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Antapani", "address": "Jl. Raya Antapani No.65", "district": "Antapani", "city": "Bandung", "province": "Jawa Barat", "capacity": 14}', true),
            'form2_data'          => json_decode('{"name": "Admin 38", "email": "admin.38@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 38", "email": "gizi.38@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 38", "email": "logistik.38@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.905353,
            'longitude'           => 107.650104,
            'confirmed_latitude'  => -6.906332,
            'confirmed_longitude' => 107.649582,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MI Negeri 20 Gedebage', 'npsn' => '20304804', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Kebon Jati No.94, Gedebage', 'city' => 'Kota Bandung', 'district' => 'Gedebage', 'latitude' => -6.90744, 'longitude' => 107.652682, 'jumlah_porsi' => 402, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 22 Batununggal', 'npsn' => '20301531', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Cibadak No.46, Batununggal', 'city' => 'Kota Bandung', 'district' => 'Batununggal', 'latitude' => -6.905644, 'longitude' => 107.641329, 'jumlah_porsi' => 278, 'data_source' => 'database'],
            ['school_name' => 'SMAN 38 Ujungberung', 'npsn' => '20304603', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jalan Cibadak No.117, Ujungberung', 'city' => 'Kota Bandung', 'district' => 'Ujungberung', 'latitude' => -6.901335, 'longitude' => 107.658622, 'jumlah_porsi' => 987, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-039
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-039',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Rancasari", "address": "Jl. Raya Rancasari No.73", "district": "Rancasari", "city": "Bandung", "province": "Jawa Barat", "capacity": 10}', true),
            'form2_data'          => json_decode('{"name": "Admin 39", "email": "admin.39@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 39", "email": "gizi.39@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 39", "email": "logistik.39@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.967428,
            'longitude'           => 107.656404,
            'confirmed_latitude'  => -6.966531,
            'confirmed_longitude' => 107.656723,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMKN 12 Margacinta', 'npsn' => '20303244', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Asia Afrika No.33, Margacinta', 'city' => 'Kota Bandung', 'district' => 'Margacinta', 'latitude' => -6.962199, 'longitude' => 107.645827, 'jumlah_porsi' => 1333, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 25 Bojongsoang', 'npsn' => '20311303', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jalan Setiabudi No.100, Bojongsoang', 'city' => 'Kabupaten Bandung', 'district' => 'Bojongsoang', 'latitude' => -6.96491, 'longitude' => 107.642244, 'jumlah_porsi' => 1345, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-040
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-040',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Bandung Kulon", "address": "Jl. Raya Bandung Kulon No.29", "district": "Bandung Kulon", "city": "Bandung", "province": "Jawa Barat", "capacity": 13}', true),
            'form2_data'          => json_decode('{"name": "Admin 40", "email": "admin.40@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 40", "email": "gizi.40@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 40", "email": "logistik.40@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.952641,
            'longitude'           => 107.584835,
            'confirmed_latitude'  => -6.952118,
            'confirmed_longitude' => 107.584298,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Negeri 49 Rancasari', 'npsn' => '20303796', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Sudirman No.81, Rancasari', 'city' => 'Kota Bandung', 'district' => 'Rancasari', 'latitude' => -6.963384, 'longitude' => 107.599085, 'jumlah_porsi' => 1027, 'data_source' => 'database'],
            ['school_name' => 'SMK Negeri 7 Rancasari', 'npsn' => '20303277', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Pasirkaliki No.123, Rancasari', 'city' => 'Kota Bandung', 'district' => 'Rancasari', 'latitude' => -6.953218, 'longitude' => 107.602812, 'jumlah_porsi' => 305, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-041
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-041',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Sukasari", "address": "Jl. Raya Sukasari No.47", "district": "Sukasari", "city": "Bandung", "province": "Jawa Barat", "capacity": 11}', true),
            'form2_data'          => json_decode('{"name": "Admin 41", "email": "admin.41@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 41", "email": "gizi.41@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 41", "email": "logistik.41@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.876306,
            'longitude'           => 107.560428,
            'confirmed_latitude'  => -6.87653,
            'confirmed_longitude' => 107.561264,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SDN 9 Bojongloa Kaler', 'npsn' => '20305322', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Diponegoro No.170, Bojongloa Kaler', 'city' => 'Kota Bandung', 'district' => 'Bojongloa Kaler', 'latitude' => -6.880192, 'longitude' => 107.559952, 'jumlah_porsi' => 466, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 40 Bojongloa Kaler', 'npsn' => '20305440', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jalan Cihampelas No.165, Bojongloa Kaler', 'city' => 'Kota Bandung', 'district' => 'Bojongloa Kaler', 'latitude' => -6.874918, 'longitude' => 107.556088, 'jumlah_porsi' => 1180, 'data_source' => 'database'],
            ['school_name' => 'SMP Cimahi Tengah 15', 'npsn' => '20306892', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jalan Pasirkaliki No.50, Cimahi Tengah', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Tengah', 'latitude' => -6.882144, 'longitude' => 107.564344, 'jumlah_porsi' => 551, 'data_source' => 'database'],
            ['school_name' => 'MI Negeri 11 Bojongloa Kidul', 'npsn' => '20306175', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jalan Sudirman No.112, Bojongloa Kidul', 'city' => 'Kota Bandung', 'district' => 'Bojongloa Kidul', 'latitude' => -6.881379, 'longitude' => 107.566024, 'jumlah_porsi' => 510, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-042
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-042',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Babakan Ciparay", "address": "Jl. Raya Babakan Ciparay No.67", "district": "Babakan Ciparay", "city": "Bandung", "province": "Jawa Barat", "capacity": 9}', true),
            'form2_data'          => json_decode('{"name": "Admin 42", "email": "admin.42@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 42", "email": "gizi.42@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 42", "email": "logistik.42@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.934601,
            'longitude'           => 107.576485,
            'confirmed_latitude'  => -6.934334,
            'confirmed_longitude' => 107.576032,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMP Negeri 5 Babakan Ciparay', 'npsn' => '20305511', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jalan Buah Batu No.38, Babakan Ciparay', 'city' => 'Kota Bandung', 'district' => 'Babakan Ciparay', 'latitude' => -6.923477, 'longitude' => 107.56775, 'jumlah_porsi' => 699, 'data_source' => 'database'],
            ['school_name' => 'SD Andir 1', 'npsn' => '20306018', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jalan Pelajar No.197, Andir', 'city' => 'Kota Bandung', 'district' => 'Andir', 'latitude' => -6.919609, 'longitude' => 107.566688, 'jumlah_porsi' => 435, 'data_source' => 'database'],
            ['school_name' => 'SMP Negeri 15 Bandung Wetan', 'npsn' => '20301517', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Setiabudhi No.85, Bandung Wetan', 'city' => 'Kota Bandung', 'district' => 'Bandung Wetan', 'latitude' => -6.921967, 'longitude' => 107.591555, 'jumlah_porsi' => 385, 'data_source' => 'database'],
            ['school_name' => 'MTs Negeri 10 Bojongloa Kidul', 'npsn' => '20305653', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Sudirman No.22, Bojongloa Kidul', 'city' => 'Kota Bandung', 'district' => 'Bojongloa Kidul', 'latitude' => -6.921988, 'longitude' => 107.558034, 'jumlah_porsi' => 481, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-043
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-043',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Buahbatu Timur", "address": "Jl. Raya Buah Batu No.49", "district": "Buah Batu", "city": "Bandung", "province": "Jawa Barat", "capacity": 14}', true),
            'form2_data'          => json_decode('{"name": "Admin 43", "email": "admin.43@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 43", "email": "gizi.43@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 43", "email": "logistik.43@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.939214,
            'longitude'           => 107.635702,
            'confirmed_latitude'  => -6.939483,
            'confirmed_longitude' => 107.636009,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MA Negeri 31 Buah Batu', 'npsn' => '20302652', 'level' => 'MA', 'school_status' => 'public', 'address' => 'Jl. Soekarno-Hatta No.58, Buah Batu', 'city' => 'Kota Bandung', 'district' => 'Buah Batu', 'latitude' => -6.941884, 'longitude' => 107.635314, 'jumlah_porsi' => 141, 'data_source' => 'database'],
            ['school_name' => 'SMPN 31 Buah Batu', 'npsn' => '20303730', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jalan Ahmad Yani No.83, Buah Batu', 'city' => 'Kota Bandung', 'district' => 'Buah Batu', 'latitude' => -6.940804, 'longitude' => 107.63948, 'jumlah_porsi' => 437, 'data_source' => 'database'],
            ['school_name' => 'MTs Persatuan 5', 'npsn' => '20302872', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jl. Diponegoro No.179, Bandung Kidul', 'city' => 'Kota Bandung', 'district' => 'Bandung Kidul', 'latitude' => -6.935271, 'longitude' => 107.639075, 'jumlah_porsi' => 223, 'data_source' => 'database'],
            ['school_name' => 'SMK Bandung Kidul 13', 'npsn' => '20303206', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jalan Cibadak No.186, Bandung Kidul', 'city' => 'Kota Bandung', 'district' => 'Bandung Kidul', 'latitude' => -6.945157, 'longitude' => 107.634169, 'jumlah_porsi' => 779, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-044
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-044',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Ujungberung", "address": "Jl. Raya Ujungberung No.52", "district": "Ujungberung", "city": "Bandung", "province": "Jawa Barat", "capacity": 11}', true),
            'form2_data'          => json_decode('{"name": "Admin 44", "email": "admin.44@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 44", "email": "gizi.44@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 44", "email": "logistik.44@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.909833,
            'longitude'           => 107.668763,
            'confirmed_latitude'  => -6.910127,
            'confirmed_longitude' => 107.669498,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MI Nurul Huda Arcamanik 5', 'npsn' => '20304947', 'level' => 'MI', 'school_status' => 'private', 'address' => 'Jl. Gedebage No.186, Arcamanik', 'city' => 'Kota Bandung', 'district' => 'Arcamanik', 'latitude' => -6.910573, 'longitude' => 107.669414, 'jumlah_porsi' => 670, 'data_source' => 'database'],
            ['school_name' => 'MA Negeri 29 Cibiru', 'npsn' => '20304246', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Ahmad Yani No.142, Cibiru', 'city' => 'Kota Bandung', 'district' => 'Cibiru', 'latitude' => -6.905791, 'longitude' => 107.668314, 'jumlah_porsi' => 859, 'data_source' => 'database'],
            ['school_name' => 'MTs Al-Falah Ujungberung', 'npsn' => '20304145', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jalan Pemuda No.117, Ujungberung', 'city' => 'Kota Bandung', 'district' => 'Ujungberung', 'latitude' => -6.917229, 'longitude' => 107.668661, 'jumlah_porsi' => 620, 'data_source' => 'database'],
            ['school_name' => 'SD Negeri 35 Ujungberung', 'npsn' => '20305029', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jalan Soekarno-Hatta No.173, Ujungberung', 'city' => 'Kota Bandung', 'district' => 'Ujungberung', 'latitude' => -6.90654, 'longitude' => 107.66205, 'jumlah_porsi' => 490, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-045
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-045',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Kutawaringin", "address": "Jl. Raya Kutawaringin No.18", "district": "Kutawaringin", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 8}', true),
            'form2_data'          => json_decode('{"name": "Admin 45", "email": "admin.45@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 45", "email": "gizi.45@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 45", "email": "logistik.45@sppg.test", "password": "password123"}}', true),
            'latitude'            => -7.077073,
            'longitude'           => 107.563872,
            'confirmed_latitude'  => -7.077193,
            'confirmed_longitude' => 107.563332,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMAN 4 Katapang', 'npsn' => '20312611', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Buah Batu No.138, Katapang', 'city' => 'Kabupaten Bandung', 'district' => 'Katapang', 'latitude' => -7.047926, 'longitude' => 107.546088, 'jumlah_porsi' => 799, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 21 Cangkuang', 'npsn' => '20311916', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jalan Merdeka No.182, Cangkuang', 'city' => 'Kabupaten Bandung', 'district' => 'Cangkuang', 'latitude' => -7.050806, 'longitude' => 107.539018, 'jumlah_porsi' => 423, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-046
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-046',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Cangkuang", "address": "Jl. Raya Cangkuang No.92", "district": "Cangkuang", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 13}', true),
            'form2_data'          => json_decode('{"name": "Admin 46", "email": "admin.46@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 46", "email": "gizi.46@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 46", "email": "logistik.46@sppg.test", "password": "password123"}}', true),
            'latitude'            => -7.052885,
            'longitude'           => 107.606869,
            'confirmed_latitude'  => -7.052251,
            'confirmed_longitude' => 107.607191,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Baleendah 31', 'npsn' => '20310511', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Gatot Subroto No.4, Baleendah', 'city' => 'Kabupaten Bandung', 'district' => 'Baleendah', 'latitude' => -6.995578, 'longitude' => 107.613119, 'jumlah_porsi' => 440, 'data_source' => 'database'],
            ['school_name' => 'SMAN 4 Katapang', 'npsn' => '20312611', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Buah Batu No.138, Katapang', 'city' => 'Kabupaten Bandung', 'district' => 'Katapang', 'latitude' => -7.047926, 'longitude' => 107.546088, 'jumlah_porsi' => 799, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-047
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-047',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Cisarua", "address": "Jl. Raya Cisarua No.62", "district": "Cisarua", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 15}', true),
            'form2_data'          => json_decode('{"name": "Admin 47", "email": "admin.47@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 47", "email": "gizi.47@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 47", "email": "logistik.47@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.796888,
            'longitude'           => 107.637203,
            'confirmed_latitude'  => -6.79693,
            'confirmed_longitude' => 107.637385,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Teknologi Parongpong', 'npsn' => '20309981', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Pemuda No.1, Parongpong', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Parongpong', 'latitude' => -6.796152, 'longitude' => 107.636048, 'jumlah_porsi' => 1148, 'data_source' => 'database'],
            ['school_name' => 'MA Negeri 27 Cisarua', 'npsn' => '20309659', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Pemuda No.175, Cisarua', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Cisarua', 'latitude' => -6.79623, 'longitude' => 107.630313, 'jumlah_porsi' => 367, 'data_source' => 'database'],
            ['school_name' => 'SMKN 4 Parongpong', 'npsn' => '20310415', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Gedebage No.155, Parongpong', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Parongpong', 'latitude' => -6.804437, 'longitude' => 107.630339, 'jumlah_porsi' => 585, 'data_source' => 'database'],
            ['school_name' => 'SMP Cisarua 19', 'npsn' => '20309440', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Buah Batu No.164, Cisarua', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Cisarua', 'latitude' => -6.788538, 'longitude' => 107.627801, 'jumlah_porsi' => 627, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-048
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-048',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Baros Cimahi", "address": "Jl. Raya Baros No.21", "district": "Baros", "city": "Kota Cimahi", "province": "Jawa Barat", "capacity": 17}', true),
            'form2_data'          => json_decode('{"name": "Admin 48", "email": "admin.48@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 48", "email": "gizi.48@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 48", "email": "logistik.48@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.899776,
            'longitude'           => 107.515431,
            'confirmed_latitude'  => -6.900507,
            'confirmed_longitude' => 107.515456,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Teknologi Cimahi Selatan', 'npsn' => '20306940', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Pelajar No.155, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.894546, 'longitude' => 107.526389, 'jumlah_porsi' => 1184, 'data_source' => 'database'],
            ['school_name' => 'SMA Cimahi Selatan 37', 'npsn' => '20307271', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Gatot Subroto No.110, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.901942, 'longitude' => 107.531986, 'jumlah_porsi' => 1368, 'data_source' => 'database'],
            ['school_name' => 'MI Al-Hikmah 10', 'npsn' => '20306692', 'level' => 'MI', 'school_status' => 'private', 'address' => 'Jl. Kebon Jati No.170, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.894382, 'longitude' => 107.531823, 'jumlah_porsi' => 501, 'data_source' => 'database'],
            ['school_name' => 'SD Negeri 24 Cimahi Selatan', 'npsn' => '20307304', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Setiabudhi No.121, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.899674, 'longitude' => 107.533841, 'jumlah_porsi' => 482, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-049
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-049',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Banyuresmi", "address": "Jl. Raya Banyuresmi No.52", "district": "Banyuresmi", "city": "Kabupaten Garut", "province": "Jawa Barat", "capacity": 10}', true),
            'form2_data'          => json_decode('{"name": "Admin 49", "email": "admin.49@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 49", "email": "gizi.49@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 49", "email": "logistik.49@sppg.test", "password": "password123"}}', true),
            'latitude'            => -7.154441,
            'longitude'           => 107.777583,
            'confirmed_latitude'  => -7.154242,
            'confirmed_longitude' => 107.77771,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MI Islamiyah Tarogong Kidul', 'npsn' => '20314755', 'level' => 'MI', 'school_status' => 'private', 'address' => 'Jalan Buah Batu No.57, Tarogong Kidul', 'city' => 'Kabupaten Garut', 'district' => 'Tarogong Kidul', 'latitude' => -7.212452, 'longitude' => 107.86843, 'jumlah_porsi' => 608, 'data_source' => 'database'],
            ['school_name' => 'SMK Teknologi Tarogong Kaler', 'npsn' => '20314250', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Asia Afrika No.192, Tarogong Kaler', 'city' => 'Kabupaten Garut', 'district' => 'Tarogong Kaler', 'latitude' => -7.217291, 'longitude' => 107.867918, 'jumlah_porsi' => 408, 'data_source' => 'database'],
        ]);
        $created++;

        // GREEN | BULK-20260607-050
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-050',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Margahayu", "address": "Jl. Raya Margahayu No.28", "district": "Margahayu", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin 50", "email": "admin.50@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi 50", "email": "gizi.50@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik 50", "email": "logistik.50@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.976215,
            'longitude'           => 107.54728,
            'confirmed_latitude'  => -6.976317,
            'confirmed_longitude' => 107.548266,
            'point_status'        => 'green',
            'map_confirmed'       => true,
            'status'              => 'registered',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Negeri 44 Banjaran', 'npsn' => '20312701', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.16, Banjaran', 'city' => 'Kabupaten Bandung', 'district' => 'Banjaran', 'latitude' => -7.003379, 'longitude' => 107.539991, 'jumlah_porsi' => 459, 'data_source' => 'database'],
        ]);
        $created++;

        // ── 50 KUNING ─────────────────────────────────────────────
        // YELLOW | BULK-20260607-051
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-051',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Sukajadi Perluasan 1", "address": "Jl. Sukajadi Baru No.40", "district": "Sukajadi", "city": "Bandung", "province": "Jawa Barat", "capacity": 15}', true),
            'form2_data'          => json_decode('{"name": "Admin Y01", "email": "admin.y01@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y01", "email": "gizi.y01@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y01", "email": "logistik.y01@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.844247,
            'longitude'           => 107.610321,
            'confirmed_latitude'  => -6.845189,
            'confirmed_longitude' => 107.611218,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Lembang 16', 'npsn' => '20310018', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Diponegoro No.153, Lembang', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Lembang', 'latitude' => -6.835212, 'longitude' => 107.615323, 'jumlah_porsi' => 299, 'data_source' => 'database'],
            ['school_name' => 'SMP Negeri 16 Parongpong', 'npsn' => '20309109', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Rancabolang No.60, Parongpong', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Parongpong', 'latitude' => -6.83419, 'longitude' => 107.60528, 'jumlah_porsi' => 583, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-052
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-052',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Buah Batu Perluasan 2", "address": "Jl. Buah Batu Baru No.25", "district": "Buah Batu", "city": "Bandung", "province": "Jawa Barat", "capacity": 15}', true),
            'form2_data'          => json_decode('{"name": "Admin Y02", "email": "admin.y02@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.921285,
            'longitude'           => 107.600953,
            'confirmed_latitude'  => -6.922851,
            'confirmed_longitude' => 107.599762,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MTs Al-Falah Sumur Bandung', 'npsn' => '20301227', 'level' => 'MTs', 'school_status' => 'private', 'address' => 'Jalan Merdeka No.1, Sumur Bandung', 'city' => 'Kota Bandung', 'district' => 'Sumur Bandung', 'latitude' => -6.918997, 'longitude' => 107.594898, 'jumlah_porsi' => 368, 'data_source' => 'database'],
            ['school_name' => 'MI Al-Hikmah 15', 'npsn' => '20302293', 'level' => 'MI', 'school_status' => 'public', 'address' => 'Jl. Pelajar No.17, Bandung Wetan', 'city' => 'Kota Bandung', 'district' => 'Bandung Wetan', 'latitude' => -6.914602, 'longitude' => 107.605792, 'jumlah_porsi' => 577, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-053
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-053',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Gedebage Perluasan 3", "address": "Jl. Gedebage Baru No.12", "district": "Gedebage", "city": "Bandung", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin Y03", "email": "admin.y03@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y03", "email": "gizi.y03@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y03", "email": "logistik.y03@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.962184,
            'longitude'           => 107.702536,
            'confirmed_latitude'  => -6.961725,
            'confirmed_longitude' => 107.700692,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MTs Al-Falah Arcamanik', 'npsn' => '20304541', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jalan Cibadak No.171, Arcamanik', 'city' => 'Kota Bandung', 'district' => 'Arcamanik', 'latitude' => -6.946519, 'longitude' => 107.691587, 'jumlah_porsi' => 394, 'data_source' => 'database'],
            ['school_name' => 'MTs Negeri 16 Cibiru', 'npsn' => '20304454', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Veteran No.45, Cibiru', 'city' => 'Kota Bandung', 'district' => 'Cibiru', 'latitude' => -6.942758, 'longitude' => 107.695192, 'jumlah_porsi' => 346, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-054
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-054',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Coblong Perluasan 4", "address": "Jl. Coblong Baru No.43", "district": "Coblong", "city": "Bandung", "province": "Jawa Barat", "capacity": 7}', true),
            'form2_data'          => json_decode('{"name": "Admin Y04", "email": "admin.y04@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.932395,
            'longitude'           => 107.619486,
            'confirmed_latitude'  => -6.931981,
            'confirmed_longitude' => 107.620796,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SD Negeri 30 Batununggal', 'npsn' => '20302116', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Diponegoro No.117, Batununggal', 'city' => 'Kota Bandung', 'district' => 'Batununggal', 'latitude' => -6.92982, 'longitude' => 107.613075, 'jumlah_porsi' => 643, 'data_source' => 'database'],
            ['school_name' => 'MTs Al-Falah Lengkong', 'npsn' => '20301778', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jalan Asia Afrika No.33, Lengkong', 'city' => 'Kota Bandung', 'district' => 'Lengkong', 'latitude' => -6.926657, 'longitude' => 107.62398, 'jumlah_porsi' => 488, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-055
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-055',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Regol Perluasan 5", "address": "Jl. Regol Baru No.3", "district": "Regol", "city": "Bandung", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin Y05", "email": "admin.y05@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y05", "email": "gizi.y05@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y05", "email": "logistik.y05@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.943878,
            'longitude'           => 107.641814,
            'confirmed_latitude'  => -6.944162,
            'confirmed_longitude' => 107.643568,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MA Negeri 37 Margacinta', 'npsn' => '20303677', 'level' => 'MA', 'school_status' => 'private', 'address' => 'Jl. Pasirkaliki No.133, Margacinta', 'city' => 'Kota Bandung', 'district' => 'Margacinta', 'latitude' => -6.94181, 'longitude' => 107.642657, 'jumlah_porsi' => 285, 'data_source' => 'database'],
            ['school_name' => 'SMK Negeri 39 Bandung Kidul', 'npsn' => '20302799', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Gatot Subroto No.28, Bandung Kidul', 'city' => 'Kota Bandung', 'district' => 'Bandung Kidul', 'latitude' => -6.945747, 'longitude' => 107.643355, 'jumlah_porsi' => 1379, 'data_source' => 'database'],
            ['school_name' => 'SMPN 31 Buah Batu', 'npsn' => '20303730', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jalan Ahmad Yani No.83, Buah Batu', 'city' => 'Kota Bandung', 'district' => 'Buah Batu', 'latitude' => -6.940804, 'longitude' => 107.63948, 'jumlah_porsi' => 437, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-056
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-056',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Andir Perluasan 6", "address": "Jl. Andir Baru No.9", "district": "Andir", "city": "Bandung", "province": "Jawa Barat", "capacity": 11}', true),
            'form2_data'          => json_decode('{"name": "Admin Y06", "email": "admin.y06@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.905574,
            'longitude'           => 107.55047,
            'confirmed_latitude'  => -6.904511,
            'confirmed_longitude' => 107.55211,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMPN 29 Babakan Ciparay', 'npsn' => '20306382', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jalan Kebon Jati No.15, Babakan Ciparay', 'city' => 'Kota Bandung', 'district' => 'Babakan Ciparay', 'latitude' => -6.908003, 'longitude' => 107.556815, 'jumlah_porsi' => 572, 'data_source' => 'database'],
            ['school_name' => 'MTs Negeri 40 Cimahi Tengah', 'npsn' => '20307674', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jalan Rancabolang No.15, Cimahi Tengah', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Tengah', 'latitude' => -6.897971, 'longitude' => 107.547487, 'jumlah_porsi' => 472, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-057
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-057',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Bojongloa Kidul Perluasan 7", "address": "Jl. Bojongloa Kidul Baru No.13", "district": "Bojongloa Kidul", "city": "Bandung", "province": "Jawa Barat", "capacity": 14}', true),
            'form2_data'          => json_decode('{"name": "Admin Y07", "email": "admin.y07@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.926539,
            'longitude'           => 107.626987,
            'confirmed_latitude'  => -6.925564,
            'confirmed_longitude' => 107.625015,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MTs Al-Falah Lengkong', 'npsn' => '20301778', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jalan Asia Afrika No.33, Lengkong', 'city' => 'Kota Bandung', 'district' => 'Lengkong', 'latitude' => -6.926657, 'longitude' => 107.62398, 'jumlah_porsi' => 488, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 28 Regol', 'npsn' => '20301598', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jalan Kebon Jati No.49, Regol', 'city' => 'Kota Bandung', 'district' => 'Regol', 'latitude' => -6.923258, 'longitude' => 107.626368, 'jumlah_porsi' => 203, 'data_source' => 'database'],
            ['school_name' => 'SMK Teknologi Batununggal', 'npsn' => '20301838', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Gatot Subroto No.74, Batununggal', 'city' => 'Kota Bandung', 'district' => 'Batununggal', 'latitude' => -6.925666, 'longitude' => 107.630559, 'jumlah_porsi' => 859, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-058
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-058',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Batununggal Perluasan 8", "address": "Jl. Batununggal Baru No.27", "district": "Batununggal", "city": "Bandung", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin Y08", "email": "admin.y08@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.895005,
            'longitude'           => 107.604644,
            'confirmed_latitude'  => -6.896386,
            'confirmed_longitude' => 107.60525,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Negeri 13 Sukasari', 'npsn' => '20300901', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Gedebage No.111, Sukasari', 'city' => 'Kota Bandung', 'district' => 'Sukasari', 'latitude' => -6.892876, 'longitude' => 107.60516, 'jumlah_porsi' => 310, 'data_source' => 'database'],
            ['school_name' => 'MA Assalam 49', 'npsn' => '20306324', 'level' => 'MA', 'school_status' => 'private', 'address' => 'Jalan Gedebage No.145, Babakan Ciparay', 'city' => 'Kota Bandung', 'district' => 'Babakan Ciparay', 'latitude' => -6.892271, 'longitude' => 107.606491, 'jumlah_porsi' => 430, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-059
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-059',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Margacinta Perluasan 9", "address": "Jl. Margacinta Baru No.44", "district": "Margacinta", "city": "Bandung", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin Y09", "email": "admin.y09@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.919119,
            'longitude'           => 107.64406,
            'confirmed_latitude'  => -6.920304,
            'confirmed_longitude' => 107.644268,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMP Negeri 30 Gedebage', 'npsn' => '20305214', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Rancabolang No.164, Gedebage', 'city' => 'Kota Bandung', 'district' => 'Gedebage', 'latitude' => -6.914749, 'longitude' => 107.648938, 'jumlah_porsi' => 441, 'data_source' => 'database'],
            ['school_name' => 'SDN 37 Ujungberung', 'npsn' => '20304681', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jalan Raya No.169, Ujungberung', 'city' => 'Kota Bandung', 'district' => 'Ujungberung', 'latitude' => -6.925632, 'longitude' => 107.648216, 'jumlah_porsi' => 150, 'data_source' => 'database'],
            ['school_name' => 'MI Al-Hikmah 25', 'npsn' => '20302555', 'level' => 'MI', 'school_status' => 'public', 'address' => 'Jl. Cihampelas No.84, Regol', 'city' => 'Kota Bandung', 'district' => 'Regol', 'latitude' => -6.918784, 'longitude' => 107.635671, 'jumlah_porsi' => 384, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-060
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-060',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Arcamanik Perluasan 10", "address": "Jl. Arcamanik Baru No.20", "district": "Arcamanik", "city": "Bandung", "province": "Jawa Barat", "capacity": 14}', true),
            'form2_data'          => json_decode('{"name": "Admin Y10", "email": "admin.y10@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y10", "email": "gizi.y10@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y10", "email": "logistik.y10@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.935589,
            'longitude'           => 107.631805,
            'confirmed_latitude'  => -6.934492,
            'confirmed_longitude' => 107.631398,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MA Negeri 31 Buah Batu', 'npsn' => '20302652', 'level' => 'MA', 'school_status' => 'public', 'address' => 'Jl. Soekarno-Hatta No.58, Buah Batu', 'city' => 'Kota Bandung', 'district' => 'Buah Batu', 'latitude' => -6.941884, 'longitude' => 107.635314, 'jumlah_porsi' => 141, 'data_source' => 'database'],
            ['school_name' => 'MTs Persatuan 5', 'npsn' => '20302872', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jl. Diponegoro No.179, Bandung Kidul', 'city' => 'Kota Bandung', 'district' => 'Bandung Kidul', 'latitude' => -6.935271, 'longitude' => 107.639075, 'jumlah_porsi' => 223, 'data_source' => 'database'],
            ['school_name' => 'SMPN 31 Buah Batu', 'npsn' => '20303730', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jalan Ahmad Yani No.83, Buah Batu', 'city' => 'Kota Bandung', 'district' => 'Buah Batu', 'latitude' => -6.940804, 'longitude' => 107.63948, 'jumlah_porsi' => 437, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-061
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-061',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Cimahi Utara Perluasan 11", "address": "Jl. Cimahi Utara Baru No.45", "district": "Cimahi Utara", "city": "Cimahi", "province": "Jawa Barat", "capacity": 7}', true),
            'form2_data'          => json_decode('{"name": "Admin Y11", "email": "admin.y11@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y11", "email": "gizi.y11@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y11", "email": "logistik.y11@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.838702,
            'longitude'           => 107.56173,
            'confirmed_latitude'  => -6.840301,
            'confirmed_longitude' => 107.561763,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMPN 22 Cimahi Selatan', 'npsn' => '20307493', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Soekarno-Hatta No.112, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.853947, 'longitude' => 107.553247, 'jumlah_porsi' => 495, 'data_source' => 'database'],
            ['school_name' => 'MA Assalam 7', 'npsn' => '20306753', 'level' => 'MA', 'school_status' => 'public', 'address' => 'Jl. Gedebage No.87, Cimahi Tengah', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Tengah', 'latitude' => -6.854654, 'longitude' => 107.546426, 'jumlah_porsi' => 485, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-062
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-062',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Cimahi Tengah Perluasan 12", "address": "Jl. Cimahi Tengah Baru No.25", "district": "Cimahi Tengah", "city": "Cimahi", "province": "Jawa Barat", "capacity": 15}', true),
            'form2_data'          => json_decode('{"name": "Admin Y12", "email": "admin.y12@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y12", "email": "gizi.y12@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y12", "email": "logistik.y12@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.92399,
            'longitude'           => 107.549059,
            'confirmed_latitude'  => -6.925969,
            'confirmed_longitude' => 107.547423,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MTs Negeri 10 Bojongloa Kidul', 'npsn' => '20305653', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Sudirman No.22, Bojongloa Kidul', 'city' => 'Kota Bandung', 'district' => 'Bojongloa Kidul', 'latitude' => -6.921988, 'longitude' => 107.558034, 'jumlah_porsi' => 481, 'data_source' => 'database'],
            ['school_name' => 'SMP Negeri 16 Bojongloa Kaler', 'npsn' => '20306243', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Cibadak No.51, Bojongloa Kaler', 'city' => 'Kota Bandung', 'district' => 'Bojongloa Kaler', 'latitude' => -6.917649, 'longitude' => 107.561099, 'jumlah_porsi' => 177, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-063
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-063',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Cimahi Selatan Perluasan 13", "address": "Jl. Cimahi Selatan Baru No.17", "district": "Cimahi Selatan", "city": "Cimahi", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin Y13", "email": "admin.y13@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.871728,
            'longitude'           => 107.560886,
            'confirmed_latitude'  => -6.873408,
            'confirmed_longitude' => 107.559037,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Negeri 40 Bojongloa Kaler', 'npsn' => '20305440', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jalan Cihampelas No.165, Bojongloa Kaler', 'city' => 'Kota Bandung', 'district' => 'Bojongloa Kaler', 'latitude' => -6.874918, 'longitude' => 107.556088, 'jumlah_porsi' => 1180, 'data_source' => 'database'],
            ['school_name' => 'MA Negeri 36 Cimahi Tengah', 'npsn' => '20306704', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Pahlawan No.172, Cimahi Tengah', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Tengah', 'latitude' => -6.864036, 'longitude' => 107.564343, 'jumlah_porsi' => 1278, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-064
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-064',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Padalarang Perluasan 14", "address": "Jl. Padalarang Baru No.50", "district": "Padalarang", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 15}', true),
            'form2_data'          => json_decode('{"name": "Admin Y14", "email": "admin.y14@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y14", "email": "gizi.y14@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y14", "email": "logistik.y14@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.806433,
            'longitude'           => 107.432643,
            'confirmed_latitude'  => -6.805872,
            'confirmed_longitude' => 107.432399,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MTs Negeri 33 Batujajar', 'npsn' => '20308514', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jalan Buah Batu No.175, Batujajar', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Batujajar', 'latitude' => -6.833998, 'longitude' => 107.449784, 'jumlah_porsi' => 199, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 10 Cikalongwetan', 'npsn' => '20308389', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Gatot Subroto No.99, Cikalongwetan', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Cikalongwetan', 'latitude' => -6.820315, 'longitude' => 107.465543, 'jumlah_porsi' => 527, 'data_source' => 'database'],
            ['school_name' => 'MTs Negeri 39 Padalarang', 'npsn' => '20308758', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jalan Rancabolang No.199, Padalarang', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Padalarang', 'latitude' => -6.835758, 'longitude' => 107.455167, 'jumlah_porsi' => 152, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-065
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-065',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Lembang Perluasan 15", "address": "Jl. Lembang Baru No.3", "district": "Lembang", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 14}', true),
            'form2_data'          => json_decode('{"name": "Admin Y15", "email": "admin.y15@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y15", "email": "gizi.y15@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y15", "email": "logistik.y15@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.774042,
            'longitude'           => 107.626338,
            'confirmed_latitude'  => -6.774,
            'confirmed_longitude' => 107.627132,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMP Cisarua 19', 'npsn' => '20309440', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Buah Batu No.164, Cisarua', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Cisarua', 'latitude' => -6.788538, 'longitude' => 107.627801, 'jumlah_porsi' => 627, 'data_source' => 'database'],
            ['school_name' => 'SMPN 44 Ngamprah', 'npsn' => '20309777', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Setiabudhi No.66, Ngamprah', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Ngamprah', 'latitude' => -6.792894, 'longitude' => 107.623381, 'jumlah_porsi' => 692, 'data_source' => 'database'],
            ['school_name' => 'MA Negeri 27 Cisarua', 'npsn' => '20309659', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Pemuda No.175, Cisarua', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Cisarua', 'latitude' => -6.79623, 'longitude' => 107.630313, 'jumlah_porsi' => 367, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-066
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-066',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Batujajar Perluasan 16", "address": "Jl. Batujajar Baru No.11", "district": "Batujajar", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 11}', true),
            'form2_data'          => json_decode('{"name": "Admin Y16", "email": "admin.y16@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.845135,
            'longitude'           => 107.481355,
            'confirmed_latitude'  => -6.846031,
            'confirmed_longitude' => 107.480953,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SDN 47 Cipatat', 'npsn' => '20308347', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jalan Pahlawan No.195, Cipatat', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Cipatat', 'latitude' => -6.846998, 'longitude' => 107.473356, 'jumlah_porsi' => 426, 'data_source' => 'database'],
            ['school_name' => 'MTs Al-Falah Cipatat', 'npsn' => '20308172', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jl. Rancabolang No.142, Cipatat', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Cipatat', 'latitude' => -6.852192, 'longitude' => 107.476806, 'jumlah_porsi' => 646, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-067
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-067',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Ngamprah Perluasan 17", "address": "Jl. Ngamprah Baru No.6", "district": "Ngamprah", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 9}', true),
            'form2_data'          => json_decode('{"name": "Admin Y17", "email": "admin.y17@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y17", "email": "gizi.y17@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y17", "email": "logistik.y17@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.884411,
            'longitude'           => 107.533633,
            'confirmed_latitude'  => -6.883506,
            'confirmed_longitude' => 107.533869,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MA Assalam 24', 'npsn' => '20307563', 'level' => 'MA', 'school_status' => 'public', 'address' => 'Jalan Sudirman No.187, Cimahi Tengah', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Tengah', 'latitude' => -6.879081, 'longitude' => 107.536222, 'jumlah_porsi' => 284, 'data_source' => 'database'],
            ['school_name' => 'SMAN 8 Cimahi Selatan', 'npsn' => '20306952', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Veteran No.70, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.87774, 'longitude' => 107.540727, 'jumlah_porsi' => 1095, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-068
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-068',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Parongpong Perluasan 18", "address": "Jl. Parongpong Baru No.30", "district": "Parongpong", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 10}', true),
            'form2_data'          => json_decode('{"name": "Admin Y18", "email": "admin.y18@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y18", "email": "gizi.y18@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y18", "email": "logistik.y18@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.80626,
            'longitude'           => 107.551473,
            'confirmed_latitude'  => -6.80596,
            'confirmed_longitude' => 107.550517,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MI Islamiyah Parongpong', 'npsn' => '20309767', 'level' => 'MI', 'school_status' => 'public', 'address' => 'Jalan Sudirman No.131, Parongpong', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Parongpong', 'latitude' => -6.812947, 'longitude' => 107.58581, 'jumlah_porsi' => 235, 'data_source' => 'database'],
            ['school_name' => 'SD Negeri 35 Parongpong', 'npsn' => '20309256', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.46, Parongpong', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Parongpong', 'latitude' => -6.786545, 'longitude' => 107.584759, 'jumlah_porsi' => 506, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-069
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-069',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Dayeuhkolot Perluasan 19", "address": "Jl. Dayeuhkolot Baru No.8", "district": "Dayeuhkolot", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 7}', true),
            'form2_data'          => json_decode('{"name": "Admin Y19", "email": "admin.y19@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y19", "email": "gizi.y19@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y19", "email": "logistik.y19@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.964159,
            'longitude'           => 107.610825,
            'confirmed_latitude'  => -6.962332,
            'confirmed_longitude' => 107.612329,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MA Negeri 43 Dayeuhkolot', 'npsn' => '20310835', 'level' => 'MA', 'school_status' => 'public', 'address' => 'Jl. Setiabudi No.36, Dayeuhkolot', 'city' => 'Kabupaten Bandung', 'district' => 'Dayeuhkolot', 'latitude' => -6.961484, 'longitude' => 107.615374, 'jumlah_porsi' => 254, 'data_source' => 'database'],
            ['school_name' => 'SMA Margacinta 21', 'npsn' => '20302992', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Cibadak No.116, Margacinta', 'city' => 'Kota Bandung', 'district' => 'Margacinta', 'latitude' => -6.963845, 'longitude' => 107.618078, 'jumlah_porsi' => 976, 'data_source' => 'database'],
            ['school_name' => 'MI Negeri 16 Buah Batu', 'npsn' => '20303122', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jalan Asia Afrika No.195, Buah Batu', 'city' => 'Kota Bandung', 'district' => 'Buah Batu', 'latitude' => -6.964037, 'longitude' => 107.618698, 'jumlah_porsi' => 602, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-070
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-070',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Baleendah Perluasan 20", "address": "Jl. Baleendah Baru No.50", "district": "Baleendah", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 13}', true),
            'form2_data'          => json_decode('{"name": "Admin Y20", "email": "admin.y20@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -7.043526,
            'longitude'           => 107.634153,
            'confirmed_latitude'  => -7.044167,
            'confirmed_longitude' => 107.635255,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MI Negeri 48 Baleendah', 'npsn' => '20311225', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jalan Soekarno-Hatta No.28, Baleendah', 'city' => 'Kabupaten Bandung', 'district' => 'Baleendah', 'latitude' => -7.00208, 'longitude' => 107.654123, 'jumlah_porsi' => 555, 'data_source' => 'database'],
            ['school_name' => 'SMKN 6 Margahayu', 'npsn' => '20311024', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Cihampelas No.145, Margahayu', 'city' => 'Kabupaten Bandung', 'district' => 'Margahayu', 'latitude' => -6.993881, 'longitude' => 107.640741, 'jumlah_porsi' => 814, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-071
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-071',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Soreang Perluasan 21", "address": "Jl. Soreang Baru No.17", "district": "Soreang", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 6}', true),
            'form2_data'          => json_decode('{"name": "Admin Y21", "email": "admin.y21@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -7.072785,
            'longitude'           => 107.502549,
            'confirmed_latitude'  => -7.07225,
            'confirmed_longitude' => 107.504526,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MA Al-Azhar Cangkuang', 'npsn' => '20311722', 'level' => 'MA', 'school_status' => 'private', 'address' => 'Jalan Diponegoro No.3, Cangkuang', 'city' => 'Kabupaten Bandung', 'district' => 'Cangkuang', 'latitude' => -7.051697, 'longitude' => 107.504859, 'jumlah_porsi' => 631, 'data_source' => 'database'],
            ['school_name' => 'SMP Negeri 37 Soreang', 'npsn' => '20312092', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Soekarno-Hatta No.84, Soreang', 'city' => 'Kabupaten Bandung', 'district' => 'Soreang', 'latitude' => -7.046374, 'longitude' => 107.498975, 'jumlah_porsi' => 601, 'data_source' => 'database'],
            ['school_name' => 'SMAN 26 Soreang', 'npsn' => '20312844', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Cihampelas No.125, Soreang', 'city' => 'Kabupaten Bandung', 'district' => 'Soreang', 'latitude' => -7.039336, 'longitude' => 107.492903, 'jumlah_porsi' => 390, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-072
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-072',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Katapang Perluasan 22", "address": "Jl. Katapang Baru No.34", "district": "Katapang", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 11}', true),
            'form2_data'          => json_decode('{"name": "Admin Y22", "email": "admin.y22@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y22", "email": "gizi.y22@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y22", "email": "logistik.y22@sppg.test", "password": "password123"}}', true),
            'latitude'            => -7.037569,
            'longitude'           => 107.546265,
            'confirmed_latitude'  => -7.036766,
            'confirmed_longitude' => 107.545318,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MTs Al-Falah Cangkuang', 'npsn' => '20312909', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jl. Pahlawan No.167, Cangkuang', 'city' => 'Kabupaten Bandung', 'district' => 'Cangkuang', 'latitude' => -7.04071, 'longitude' => 107.540392, 'jumlah_porsi' => 380, 'data_source' => 'database'],
            ['school_name' => 'MI Negeri 14 Banjaran', 'npsn' => '20312551', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Rancabolang No.193, Banjaran', 'city' => 'Kabupaten Bandung', 'district' => 'Banjaran', 'latitude' => -7.043647, 'longitude' => 107.540413, 'jumlah_porsi' => 133, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 36 Cangkuang', 'npsn' => '20312493', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Cibadak No.199, Cangkuang', 'city' => 'Kabupaten Bandung', 'district' => 'Cangkuang', 'latitude' => -7.02767, 'longitude' => 107.546736, 'jumlah_porsi' => 1310, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-073
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-073',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Margaasih Perluasan 23", "address": "Jl. Margaasih Baru No.31", "district": "Margaasih", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 11}', true),
            'form2_data'          => json_decode('{"name": "Admin Y23", "email": "admin.y23@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.937419,
            'longitude'           => 107.582166,
            'confirmed_latitude'  => -6.938545,
            'confirmed_longitude' => 107.582461,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMP Negeri 15 Bandung Wetan', 'npsn' => '20301517', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Setiabudhi No.85, Bandung Wetan', 'city' => 'Kota Bandung', 'district' => 'Bandung Wetan', 'latitude' => -6.921967, 'longitude' => 107.591555, 'jumlah_porsi' => 385, 'data_source' => 'database'],
            ['school_name' => 'MTs Al-Falah Buah Batu', 'npsn' => '20303316', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jl. Kebon Jati No.162, Buah Batu', 'city' => 'Kota Bandung', 'district' => 'Buah Batu', 'latitude' => -6.939922, 'longitude' => 107.601035, 'jumlah_porsi' => 676, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-074
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-074',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Banjaran Perluasan 24", "address": "Jl. Banjaran Baru No.16", "district": "Banjaran", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 9}', true),
            'form2_data'          => json_decode('{"name": "Admin Y24", "email": "admin.y24@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y24", "email": "gizi.y24@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y24", "email": "logistik.y24@sppg.test", "password": "password123"}}', true),
            'latitude'            => -7.024311,
            'longitude'           => 107.60616,
            'confirmed_latitude'  => -7.02282,
            'confirmed_longitude' => 107.604987,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Baleendah 31', 'npsn' => '20310511', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Gatot Subroto No.4, Baleendah', 'city' => 'Kabupaten Bandung', 'district' => 'Baleendah', 'latitude' => -6.995578, 'longitude' => 107.613119, 'jumlah_porsi' => 440, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-075
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-075',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Bojongsoang Perluasan 25", "address": "Jl. Bojongsoang Baru No.50", "district": "Bojongsoang", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 15}', true),
            'form2_data'          => json_decode('{"name": "Admin Y25", "email": "admin.y25@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.965282,
            'longitude'           => 107.684156,
            'confirmed_latitude'  => -6.963601,
            'confirmed_longitude' => 107.684859,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MTs Al-Falah Arcamanik', 'npsn' => '20304541', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jalan Cibadak No.171, Arcamanik', 'city' => 'Kota Bandung', 'district' => 'Arcamanik', 'latitude' => -6.946519, 'longitude' => 107.691587, 'jumlah_porsi' => 394, 'data_source' => 'database'],
            ['school_name' => 'MTs Negeri 16 Cibiru', 'npsn' => '20304454', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Veteran No.45, Cibiru', 'city' => 'Kota Bandung', 'district' => 'Cibiru', 'latitude' => -6.942758, 'longitude' => 107.695192, 'jumlah_porsi' => 346, 'data_source' => 'database'],
            ['school_name' => 'SMP Negeri 3 Cibiru', 'npsn' => '20305169', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jalan Pasirkaliki No.30, Cibiru', 'city' => 'Kota Bandung', 'district' => 'Cibiru', 'latitude' => -6.941773, 'longitude' => 107.696087, 'jumlah_porsi' => 238, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-076
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-076',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Sumedang Utara Perluasan 26", "address": "Jl. Sumedang Utara Baru No.24", "district": "Sumedang Utara", "city": "Kabupaten Sumedang", "province": "Jawa Barat", "capacity": 14}', true),
            'form2_data'          => json_decode('{"name": "Admin Y26", "email": "admin.y26@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y26", "email": "gizi.y26@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y26", "email": "logistik.y26@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.879643,
            'longitude'           => 107.898723,
            'confirmed_latitude'  => -6.879674,
            'confirmed_longitude' => 107.900664,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Negeri 50 Tanjungsari', 'npsn' => '20313333', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Kebon Jati No.18, Tanjungsari', 'city' => 'Kabupaten Sumedang', 'district' => 'Tanjungsari', 'latitude' => -6.876281, 'longitude' => 107.904524, 'jumlah_porsi' => 323, 'data_source' => 'database'],
            ['school_name' => 'MTs Negeri 34 Sumedang Selatan', 'npsn' => '20314100', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jalan Cihampelas No.192, Sumedang Selatan', 'city' => 'Kabupaten Sumedang', 'district' => 'Sumedang Selatan', 'latitude' => -6.872438, 'longitude' => 107.897982, 'jumlah_porsi' => 236, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-077
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-077',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Jatinangor Perluasan 27", "address": "Jl. Jatinangor Baru No.26", "district": "Jatinangor", "city": "Kabupaten Sumedang", "province": "Jawa Barat", "capacity": 13}', true),
            'form2_data'          => json_decode('{"name": "Admin Y27", "email": "admin.y27@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.89479,
            'longitude'           => 107.923039,
            'confirmed_latitude'  => -6.894483,
            'confirmed_longitude' => 107.921256,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Negeri 50 Tanjungsari', 'npsn' => '20313333', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Kebon Jati No.18, Tanjungsari', 'city' => 'Kabupaten Sumedang', 'district' => 'Tanjungsari', 'latitude' => -6.876281, 'longitude' => 107.904524, 'jumlah_porsi' => 323, 'data_source' => 'database'],
            ['school_name' => 'SD Jatinangor 14', 'npsn' => '20313235', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jalan Gatot Subroto No.166, Jatinangor', 'city' => 'Kabupaten Sumedang', 'district' => 'Jatinangor', 'latitude' => -6.874077, 'longitude' => 107.941663, 'jumlah_porsi' => 345, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-078
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-078',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Sumedang Selatan Perluasan 28", "address": "Jl. Sumedang Selatan Baru No.26", "district": "Sumedang Selatan", "city": "Kabupaten Sumedang", "province": "Jawa Barat", "capacity": 14}', true),
            'form2_data'          => json_decode('{"name": "Admin Y28", "email": "admin.y28@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y28", "email": "gizi.y28@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y28", "email": "logistik.y28@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.86313,
            'longitude'           => 107.960673,
            'confirmed_latitude'  => -6.862026,
            'confirmed_longitude' => 107.960502,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Negeri 3 Sumedang Utara', 'npsn' => '20313694', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Merdeka No.121, Sumedang Utara', 'city' => 'Kabupaten Sumedang', 'district' => 'Sumedang Utara', 'latitude' => -6.857353, 'longitude' => 107.942878, 'jumlah_porsi' => 566, 'data_source' => 'database'],
            ['school_name' => 'SD Jatinangor 14', 'npsn' => '20313235', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jalan Gatot Subroto No.166, Jatinangor', 'city' => 'Kabupaten Sumedang', 'district' => 'Jatinangor', 'latitude' => -6.874077, 'longitude' => 107.941663, 'jumlah_porsi' => 345, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-079
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-079',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Tarogong Kidul Perluasan 29", "address": "Jl. Tarogong Kidul Baru No.44", "district": "Tarogong Kidul", "city": "Kabupaten Garut", "province": "Jawa Barat", "capacity": 11}', true),
            'form2_data'          => json_decode('{"name": "Admin Y29", "email": "admin.y29@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -7.190687,
            'longitude'           => 107.910977,
            'confirmed_latitude'  => -7.190363,
            'confirmed_longitude' => 107.910279,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMP Tarogong Kidul 5', 'npsn' => '20314342', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jalan Pahlawan No.24, Tarogong Kidul', 'city' => 'Kabupaten Garut', 'district' => 'Tarogong Kidul', 'latitude' => -7.198104, 'longitude' => 107.907945, 'jumlah_porsi' => 439, 'data_source' => 'database'],
            ['school_name' => 'SMAN 1 Banyuresmi', 'npsn' => '20314643', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Pahlawan No.88, Banyuresmi', 'city' => 'Kabupaten Garut', 'district' => 'Banyuresmi', 'latitude' => -7.208274, 'longitude' => 107.912818, 'jumlah_porsi' => 862, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-080
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-080',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Garut Kota Perluasan 30", "address": "Jl. Garut Kota Baru No.18", "district": "Garut Kota", "city": "Kabupaten Garut", "province": "Jawa Barat", "capacity": 14}', true),
            'form2_data'          => json_decode('{"name": "Admin Y30", "email": "admin.y30@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y30", "email": "gizi.y30@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y30", "email": "logistik.y30@sppg.test", "password": "password123"}}', true),
            'latitude'            => -7.255329,
            'longitude'           => 107.886091,
            'confirmed_latitude'  => -7.254877,
            'confirmed_longitude' => 107.885555,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMPN 45 Banyuresmi', 'npsn' => '20314965', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jalan Gatot Subroto No.61, Banyuresmi', 'city' => 'Kabupaten Garut', 'district' => 'Banyuresmi', 'latitude' => -7.241842, 'longitude' => 107.884165, 'jumlah_porsi' => 662, 'data_source' => 'database'],
            ['school_name' => 'SDN 6 Banyuresmi', 'npsn' => '20314976', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jalan Pelajar No.25, Banyuresmi', 'city' => 'Kabupaten Garut', 'district' => 'Banyuresmi', 'latitude' => -7.245636, 'longitude' => 107.872065, 'jumlah_porsi' => 398, 'data_source' => 'database'],
            ['school_name' => 'SMKN 20 Banyuresmi', 'npsn' => '20314736', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Veteran No.187, Banyuresmi', 'city' => 'Kabupaten Garut', 'district' => 'Banyuresmi', 'latitude' => -7.24693, 'longitude' => 107.901007, 'jumlah_porsi' => 1373, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-081
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-081',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Cianjur Perluasan 31", "address": "Jl. Cianjur Baru No.18", "district": "Cianjur", "city": "Kabupaten Cianjur", "province": "Jawa Barat", "capacity": 11}', true),
            'form2_data'          => json_decode('{"name": "Admin Y31", "email": "admin.y31@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y31", "email": "gizi.y31@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y31", "email": "logistik.y31@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.817765,
            'longitude'           => 107.107343,
            'confirmed_latitude'  => -6.817479,
            'confirmed_longitude' => 107.10926,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SD Cugenang 15', 'npsn' => '20315532', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jl. Cibadak No.167, Cugenang', 'city' => 'Kabupaten Cianjur', 'district' => 'Cugenang', 'latitude' => -6.824892, 'longitude' => 107.113872, 'jumlah_porsi' => 109, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 31 Cianjur', 'npsn' => '20315699', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jalan Cibadak No.164, Cianjur', 'city' => 'Kabupaten Cianjur', 'district' => 'Cianjur', 'latitude' => -6.829452, 'longitude' => 107.127854, 'jumlah_porsi' => 811, 'data_source' => 'database'],
            ['school_name' => 'SD Negeri 47 Pacet', 'npsn' => '20315273', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jl. Setiabudhi No.143, Pacet', 'city' => 'Kabupaten Cianjur', 'district' => 'Pacet', 'latitude' => -6.825548, 'longitude' => 107.130534, 'jumlah_porsi' => 382, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-082
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-082',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Cipanas Perluasan 32", "address": "Jl. Cipanas Baru No.25", "district": "Cipanas", "city": "Kabupaten Cianjur", "province": "Jawa Barat", "capacity": 10}', true),
            'form2_data'          => json_decode('{"name": "Admin Y32", "email": "admin.y32@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.817022,
            'longitude'           => 107.064147,
            'confirmed_latitude'  => -6.815822,
            'confirmed_longitude' => 107.065101,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SD Cugenang 15', 'npsn' => '20315532', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jl. Cibadak No.167, Cugenang', 'city' => 'Kabupaten Cianjur', 'district' => 'Cugenang', 'latitude' => -6.824892, 'longitude' => 107.113872, 'jumlah_porsi' => 109, 'data_source' => 'database'],
            ['school_name' => 'MI Negeri 12 Cipanas', 'npsn' => '20315471', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jalan Soekarno-Hatta No.42, Cipanas', 'city' => 'Kabupaten Cianjur', 'district' => 'Cipanas', 'latitude' => -6.801748, 'longitude' => 107.126862, 'jumlah_porsi' => 192, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-083
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-083',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Warudoyong Perluasan 33", "address": "Jl. Warudoyong Baru No.41", "district": "Warudoyong", "city": "Kota Sukabumi", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin Y33", "email": "admin.y33@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y33", "email": "gizi.y33@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y33", "email": "logistik.y33@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.920408,
            'longitude'           => 106.901009,
            'confirmed_latitude'  => -6.919526,
            'confirmed_longitude' => 106.90215,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Gunung Puyuh 28', 'npsn' => '20316373', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Pasirkaliki No.92, Gunung Puyuh', 'city' => 'Kota Sukabumi', 'district' => 'Gunung Puyuh', 'latitude' => -6.91648, 'longitude' => 106.911752, 'jumlah_porsi' => 1026, 'data_source' => 'database'],
            ['school_name' => 'MI Al-Hikmah 18', 'npsn' => '20317327', 'level' => 'MI', 'school_status' => 'public', 'address' => 'Jalan Gatot Subroto No.116, Cikole', 'city' => 'Kota Sukabumi', 'district' => 'Cikole', 'latitude' => -6.913524, 'longitude' => 106.910298, 'jumlah_porsi' => 624, 'data_source' => 'database'],
            ['school_name' => 'MI Al-Hikmah 22', 'npsn' => '20316659', 'level' => 'MI', 'school_status' => 'private', 'address' => 'Jalan Buah Batu No.69, Cikole', 'city' => 'Kota Sukabumi', 'district' => 'Cikole', 'latitude' => -6.909542, 'longitude' => 106.907835, 'jumlah_porsi' => 450, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-084
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-084',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Cikole Perluasan 34", "address": "Jl. Cikole Baru No.7", "district": "Cikole", "city": "Kota Sukabumi", "province": "Jawa Barat", "capacity": 15}', true),
            'form2_data'          => json_decode('{"name": "Admin Y34", "email": "admin.y34@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y34", "email": "gizi.y34@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y34", "email": "logistik.y34@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.910629,
            'longitude'           => 106.928741,
            'confirmed_latitude'  => -6.912389,
            'confirmed_longitude' => 106.929996,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMKN 43 Cikole', 'npsn' => '20316850', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.170, Cikole', 'city' => 'Kota Sukabumi', 'district' => 'Cikole', 'latitude' => -6.917749, 'longitude' => 106.929016, 'jumlah_porsi' => 988, 'data_source' => 'database'],
            ['school_name' => 'SMK Lembursitu 30', 'npsn' => '20317223', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Raya No.136, Lembursitu', 'city' => 'Kota Sukabumi', 'district' => 'Lembursitu', 'latitude' => -6.917232, 'longitude' => 106.932898, 'jumlah_porsi' => 1086, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-085
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-085',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Cidadap Perluasan 35", "address": "Jl. Cidadap Baru No.24", "district": "Cidadap", "city": "Bandung", "province": "Jawa Barat", "capacity": 13}', true),
            'form2_data'          => json_decode('{"name": "Admin Y35", "email": "admin.y35@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.85953,
            'longitude'           => 107.582897,
            'confirmed_latitude'  => -6.8593,
            'confirmed_longitude' => 107.584137,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Cicendo 31', 'npsn' => '20301079', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Pajajaran No.21, Cicendo', 'city' => 'Kota Bandung', 'district' => 'Cicendo', 'latitude' => -6.866009, 'longitude' => 107.586507, 'jumlah_porsi' => 339, 'data_source' => 'database'],
            ['school_name' => 'SMA Cicendo 50', 'npsn' => '20300677', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Gedebage No.122, Cicendo', 'city' => 'Kota Bandung', 'district' => 'Cicendo', 'latitude' => -6.860846, 'longitude' => 107.591767, 'jumlah_porsi' => 1280, 'data_source' => 'database'],
            ['school_name' => 'SD Sukajadi 2', 'npsn' => '20300740', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jl. Raya No.19, Sukajadi', 'city' => 'Kota Bandung', 'district' => 'Sukajadi', 'latitude' => -6.873765, 'longitude' => 107.585982, 'jumlah_porsi' => 169, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-086
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-086',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Cicendo Perluasan 36", "address": "Jl. Cicendo Baru No.50", "district": "Cicendo", "city": "Bandung", "province": "Jawa Barat", "capacity": 11}', true),
            'form2_data'          => json_decode('{"name": "Admin Y36", "email": "admin.y36@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.854022,
            'longitude'           => 107.55607,
            'confirmed_latitude'  => -6.854722,
            'confirmed_longitude' => 107.556112,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMPN 22 Cimahi Selatan', 'npsn' => '20307493', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Soekarno-Hatta No.112, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.853947, 'longitude' => 107.553247, 'jumlah_porsi' => 495, 'data_source' => 'database'],
            ['school_name' => 'MA Assalam 7', 'npsn' => '20306753', 'level' => 'MA', 'school_status' => 'public', 'address' => 'Jl. Gedebage No.87, Cimahi Tengah', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Tengah', 'latitude' => -6.854654, 'longitude' => 107.546426, 'jumlah_porsi' => 485, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-087
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-087',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Lengkong Perluasan 37", "address": "Jl. Lengkong Baru No.30", "district": "Lengkong", "city": "Bandung", "province": "Jawa Barat", "capacity": 7}', true),
            'form2_data'          => json_decode('{"name": "Admin Y37", "email": "admin.y37@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y37", "email": "gizi.y37@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y37", "email": "logistik.y37@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.948861,
            'longitude'           => 107.640346,
            'confirmed_latitude'  => -6.950282,
            'confirmed_longitude' => 107.639016,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Negeri 39 Bandung Kidul', 'npsn' => '20302799', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Gatot Subroto No.28, Bandung Kidul', 'city' => 'Kota Bandung', 'district' => 'Bandung Kidul', 'latitude' => -6.945747, 'longitude' => 107.643355, 'jumlah_porsi' => 1379, 'data_source' => 'database'],
            ['school_name' => 'SMK Bandung Kidul 13', 'npsn' => '20303206', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jalan Cibadak No.186, Bandung Kidul', 'city' => 'Kota Bandung', 'district' => 'Bandung Kidul', 'latitude' => -6.945157, 'longitude' => 107.634169, 'jumlah_porsi' => 779, 'data_source' => 'database'],
            ['school_name' => 'MA Negeri 37 Margacinta', 'npsn' => '20303677', 'level' => 'MA', 'school_status' => 'private', 'address' => 'Jl. Pasirkaliki No.133, Margacinta', 'city' => 'Kota Bandung', 'district' => 'Margacinta', 'latitude' => -6.94181, 'longitude' => 107.642657, 'jumlah_porsi' => 285, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-088
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-088',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Antapani Perluasan 38", "address": "Jl. Antapani Baru No.6", "district": "Antapani", "city": "Bandung", "province": "Jawa Barat", "capacity": 15}', true),
            'form2_data'          => json_decode('{"name": "Admin Y38", "email": "admin.y38@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.935385,
            'longitude'           => 107.661613,
            'confirmed_latitude'  => -6.936472,
            'confirmed_longitude' => 107.660881,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Ujungberung 50', 'npsn' => '20304762', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jalan Pemuda No.163, Ujungberung', 'city' => 'Kota Bandung', 'district' => 'Ujungberung', 'latitude' => -6.93675, 'longitude' => 107.661421, 'jumlah_porsi' => 480, 'data_source' => 'database'],
            ['school_name' => 'MA Negeri 32 Cibiru', 'npsn' => '20304309', 'level' => 'MA', 'school_status' => 'private', 'address' => 'Jl. Pajajaran No.152, Cibiru', 'city' => 'Kota Bandung', 'district' => 'Cibiru', 'latitude' => -6.929081, 'longitude' => 107.65324, 'jumlah_porsi' => 550, 'data_source' => 'database'],
            ['school_name' => 'SMK Negeri 49 Gedebage', 'npsn' => '20304390', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Veteran No.178, Gedebage', 'city' => 'Kota Bandung', 'district' => 'Gedebage', 'latitude' => -6.93084, 'longitude' => 107.649351, 'jumlah_porsi' => 864, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-089
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-089',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Rancasari Perluasan 39", "address": "Jl. Rancasari Baru No.50", "district": "Rancasari", "city": "Bandung", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin Y39", "email": "admin.y39@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y39", "email": "gizi.y39@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y39", "email": "logistik.y39@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.939689,
            'longitude'           => 107.651881,
            'confirmed_latitude'  => -6.940236,
            'confirmed_longitude' => 107.652229,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Negeri 49 Gedebage', 'npsn' => '20304390', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Veteran No.178, Gedebage', 'city' => 'Kota Bandung', 'district' => 'Gedebage', 'latitude' => -6.93084, 'longitude' => 107.649351, 'jumlah_porsi' => 864, 'data_source' => 'database'],
            ['school_name' => 'MA Negeri 37 Margacinta', 'npsn' => '20303677', 'level' => 'MA', 'school_status' => 'private', 'address' => 'Jl. Pasirkaliki No.133, Margacinta', 'city' => 'Kota Bandung', 'district' => 'Margacinta', 'latitude' => -6.94181, 'longitude' => 107.642657, 'jumlah_porsi' => 285, 'data_source' => 'database'],
            ['school_name' => 'SMK Ujungberung 50', 'npsn' => '20304762', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jalan Pemuda No.163, Ujungberung', 'city' => 'Kota Bandung', 'district' => 'Ujungberung', 'latitude' => -6.93675, 'longitude' => 107.661421, 'jumlah_porsi' => 480, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-090
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-090',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Bandung Kulon Perluasan 40", "address": "Jl. Bandung Kulon Baru No.47", "district": "Bandung Kulon", "city": "Bandung", "province": "Jawa Barat", "capacity": 8}', true),
            'form2_data'          => json_decode('{"name": "Admin Y40", "email": "admin.y40@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.962251,
            'longitude'           => 107.618196,
            'confirmed_latitude'  => -6.963244,
            'confirmed_longitude' => 107.619172,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Margacinta 21', 'npsn' => '20302992', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Cibadak No.116, Margacinta', 'city' => 'Kota Bandung', 'district' => 'Margacinta', 'latitude' => -6.963845, 'longitude' => 107.618078, 'jumlah_porsi' => 976, 'data_source' => 'database'],
            ['school_name' => 'MI Negeri 16 Buah Batu', 'npsn' => '20303122', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jalan Asia Afrika No.195, Buah Batu', 'city' => 'Kota Bandung', 'district' => 'Buah Batu', 'latitude' => -6.964037, 'longitude' => 107.618698, 'jumlah_porsi' => 602, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-091
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-091',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Sukasari Perluasan 41", "address": "Jl. Sukasari Baru No.5", "district": "Sukasari", "city": "Bandung", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin Y41", "email": "admin.y41@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y41", "email": "gizi.y41@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y41", "email": "logistik.y41@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.905442,
            'longitude'           => 107.56495,
            'confirmed_latitude'  => -6.905549,
            'confirmed_longitude' => 107.563165,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMPN 29 Babakan Ciparay', 'npsn' => '20306382', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jalan Kebon Jati No.15, Babakan Ciparay', 'city' => 'Kota Bandung', 'district' => 'Babakan Ciparay', 'latitude' => -6.908003, 'longitude' => 107.556815, 'jumlah_porsi' => 572, 'data_source' => 'database'],
            ['school_name' => 'SMP Negeri 16 Bojongloa Kaler', 'npsn' => '20306243', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Cibadak No.51, Bojongloa Kaler', 'city' => 'Kota Bandung', 'district' => 'Bojongloa Kaler', 'latitude' => -6.917649, 'longitude' => 107.561099, 'jumlah_porsi' => 177, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-092
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-092',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Babakan Ciparay Perluasan 42", "address": "Jl. Babakan Ciparay Baru No.40", "district": "Babakan Ciparay", "city": "Bandung", "province": "Jawa Barat", "capacity": 7}', true),
            'form2_data'          => json_decode('{"name": "Admin Y42", "email": "admin.y42@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.965217,
            'longitude'           => 107.579693,
            'confirmed_latitude'  => -6.964598,
            'confirmed_longitude' => 107.581235,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Negeri 49 Rancasari', 'npsn' => '20303796', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Sudirman No.81, Rancasari', 'city' => 'Kota Bandung', 'district' => 'Rancasari', 'latitude' => -6.963384, 'longitude' => 107.599085, 'jumlah_porsi' => 1027, 'data_source' => 'database'],
            ['school_name' => 'SMK Negeri 7 Rancasari', 'npsn' => '20303277', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Pasirkaliki No.123, Rancasari', 'city' => 'Kota Bandung', 'district' => 'Rancasari', 'latitude' => -6.953218, 'longitude' => 107.602812, 'jumlah_porsi' => 305, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-093
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-093',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Buah Batu Perluasan 43", "address": "Jl. Buah Batu Baru No.9", "district": "Buah Batu", "city": "Bandung", "province": "Jawa Barat", "capacity": 10}', true),
            'form2_data'          => json_decode('{"name": "Admin Y43", "email": "admin.y43@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y43", "email": "gizi.y43@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y43", "email": "logistik.y43@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.911096,
            'longitude'           => 107.623605,
            'confirmed_latitude'  => -6.912414,
            'confirmed_longitude' => 107.621746,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MTs Al-Falah Bandung Wetan', 'npsn' => '20301691', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jl. Gedebage No.85, Bandung Wetan', 'city' => 'Kota Bandung', 'district' => 'Bandung Wetan', 'latitude' => -6.903343, 'longitude' => 107.623896, 'jumlah_porsi' => 227, 'data_source' => 'database'],
            ['school_name' => 'SD Negeri 10 Bandung Wetan', 'npsn' => '20301989', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jl. Pemuda No.122, Bandung Wetan', 'city' => 'Kota Bandung', 'district' => 'Bandung Wetan', 'latitude' => -6.903985, 'longitude' => 107.629619, 'jumlah_porsi' => 566, 'data_source' => 'database'],
            ['school_name' => 'SMP Batununggal 31', 'npsn' => '20301935', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Gatot Subroto No.132, Batununggal', 'city' => 'Kota Bandung', 'district' => 'Batununggal', 'latitude' => -6.901143, 'longitude' => 107.621494, 'jumlah_porsi' => 195, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-094
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-094',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Ujungberung Perluasan 44", "address": "Jl. Ujungberung Baru No.36", "district": "Ujungberung", "city": "Bandung", "province": "Jawa Barat", "capacity": 13}', true),
            'form2_data'          => json_decode('{"name": "Admin Y44", "email": "admin.y44@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y44", "email": "gizi.y44@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y44", "email": "logistik.y44@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.877216,
            'longitude'           => 107.643766,
            'confirmed_latitude'  => -6.875338,
            'confirmed_longitude' => 107.644556,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMP Negeri 29 Coblong', 'npsn' => '20300947', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Rancabolang No.139, Coblong', 'city' => 'Kota Bandung', 'district' => 'Coblong', 'latitude' => -6.866836, 'longitude' => 107.635535, 'jumlah_porsi' => 342, 'data_source' => 'database'],
            ['school_name' => 'SMA Sumur Bandung 5', 'npsn' => '20301159', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Pahlawan No.90, Sumur Bandung', 'city' => 'Kota Bandung', 'district' => 'Sumur Bandung', 'latitude' => -6.889413, 'longitude' => 107.635651, 'jumlah_porsi' => 956, 'data_source' => 'database'],
            ['school_name' => 'SMAN 24 Coblong', 'npsn' => '20300599', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Merdeka No.62, Coblong', 'city' => 'Kota Bandung', 'district' => 'Coblong', 'latitude' => -6.864567, 'longitude' => 107.634335, 'jumlah_porsi' => 375, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-095
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-095',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Kutawaringin Perluasan 45", "address": "Jl. Kutawaringin Baru No.23", "district": "Kutawaringin", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 15}', true),
            'form2_data'          => json_decode('{"name": "Admin Y45", "email": "admin.y45@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y45", "email": "gizi.y45@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y45", "email": "logistik.y45@sppg.test", "password": "password123"}}', true),
            'latitude'            => -7.047074,
            'longitude'           => 107.576374,
            'confirmed_latitude'  => -7.048165,
            'confirmed_longitude' => 107.574732,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMAN 4 Katapang', 'npsn' => '20312611', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Buah Batu No.138, Katapang', 'city' => 'Kabupaten Bandung', 'district' => 'Katapang', 'latitude' => -7.047926, 'longitude' => 107.546088, 'jumlah_porsi' => 799, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 36 Cangkuang', 'npsn' => '20312493', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Cibadak No.199, Cangkuang', 'city' => 'Kabupaten Bandung', 'district' => 'Cangkuang', 'latitude' => -7.02767, 'longitude' => 107.546736, 'jumlah_porsi' => 1310, 'data_source' => 'database'],
            ['school_name' => 'MI Negeri 14 Banjaran', 'npsn' => '20312551', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Rancabolang No.193, Banjaran', 'city' => 'Kabupaten Bandung', 'district' => 'Banjaran', 'latitude' => -7.043647, 'longitude' => 107.540413, 'jumlah_porsi' => 133, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-096
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-096',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Cangkuang Perluasan 46", "address": "Jl. Cangkuang Baru No.1", "district": "Cangkuang", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 8}', true),
            'form2_data'          => json_decode('{"name": "Admin Y46", "email": "admin.y46@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y46", "email": "gizi.y46@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y46", "email": "logistik.y46@sppg.test", "password": "password123"}}', true),
            'latitude'            => -7.018493,
            'longitude'           => 107.59792,
            'confirmed_latitude'  => -7.01746,
            'confirmed_longitude' => 107.596465,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Baleendah 31', 'npsn' => '20310511', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Gatot Subroto No.4, Baleendah', 'city' => 'Kabupaten Bandung', 'district' => 'Baleendah', 'latitude' => -6.995578, 'longitude' => 107.613119, 'jumlah_porsi' => 440, 'data_source' => 'database'],
            ['school_name' => 'SMA Margahayu 11', 'npsn' => '20311339', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jalan Gatot Subroto No.69, Margahayu', 'city' => 'Kabupaten Bandung', 'district' => 'Margahayu', 'latitude' => -6.980372, 'longitude' => 107.606217, 'jumlah_porsi' => 1090, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-097
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-097',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Cisarua Perluasan 47", "address": "Jl. Cisarua Baru No.1", "district": "Cisarua", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 13}', true),
            'form2_data'          => json_decode('{"name": "Admin Y47", "email": "admin.y47@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y47", "email": "gizi.y47@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y47", "email": "logistik.y47@sppg.test", "password": "password123"}}', true),
            'latitude'            => -6.765244,
            'longitude'           => 107.626768,
            'confirmed_latitude'  => -6.764052,
            'confirmed_longitude' => 107.627156,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMP Cisarua 19', 'npsn' => '20309440', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Buah Batu No.164, Cisarua', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Cisarua', 'latitude' => -6.788538, 'longitude' => 107.627801, 'jumlah_porsi' => 627, 'data_source' => 'database'],
            ['school_name' => 'SMPN 44 Ngamprah', 'npsn' => '20309777', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Setiabudhi No.66, Ngamprah', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Ngamprah', 'latitude' => -6.792894, 'longitude' => 107.623381, 'jumlah_porsi' => 692, 'data_source' => 'database'],
            ['school_name' => 'MA Negeri 27 Cisarua', 'npsn' => '20309659', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Pemuda No.175, Cisarua', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Cisarua', 'latitude' => -6.79623, 'longitude' => 107.630313, 'jumlah_porsi' => 367, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-098
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-098',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Baros Perluasan 48", "address": "Jl. Baros Baru No.26", "district": "Baros", "city": "Kota Cimahi", "province": "Jawa Barat", "capacity": 9}', true),
            'form2_data'          => json_decode('{"name": "Admin Y48", "email": "admin.y48@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.929377,
            'longitude'           => 107.530479,
            'confirmed_latitude'  => -6.928517,
            'confirmed_longitude' => 107.531063,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Cimahi Selatan 37', 'npsn' => '20307271', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Gatot Subroto No.110, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.901942, 'longitude' => 107.531986, 'jumlah_porsi' => 1368, 'data_source' => 'database'],
            ['school_name' => 'MTs Negeri 10 Bojongloa Kidul', 'npsn' => '20305653', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Sudirman No.22, Bojongloa Kidul', 'city' => 'Kota Bandung', 'district' => 'Bojongloa Kidul', 'latitude' => -6.921988, 'longitude' => 107.558034, 'jumlah_porsi' => 481, 'data_source' => 'database'],
            ['school_name' => 'SD Negeri 24 Cimahi Selatan', 'npsn' => '20307304', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Setiabudhi No.121, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.899674, 'longitude' => 107.533841, 'jumlah_porsi' => 482, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-099
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-099',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Banyuresmi Perluasan 49", "address": "Jl. Banyuresmi Baru No.34", "district": "Banyuresmi", "city": "Kabupaten Garut", "province": "Jawa Barat", "capacity": 7}', true),
            'form2_data'          => json_decode('{"name": "Admin Y49", "email": "admin.y49@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y49", "email": "gizi.y49@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y49", "email": "logistik.y49@sppg.test", "password": "password123"}}', true),
            'latitude'            => -7.176963,
            'longitude'           => 107.794591,
            'confirmed_latitude'  => -7.175323,
            'confirmed_longitude' => 107.795821,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MI Islamiyah Tarogong Kidul', 'npsn' => '20314755', 'level' => 'MI', 'school_status' => 'private', 'address' => 'Jalan Buah Batu No.57, Tarogong Kidul', 'city' => 'Kabupaten Garut', 'district' => 'Tarogong Kidul', 'latitude' => -7.212452, 'longitude' => 107.86843, 'jumlah_porsi' => 608, 'data_source' => 'database'],
            ['school_name' => 'SMK Teknologi Tarogong Kaler', 'npsn' => '20314250', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Asia Afrika No.192, Tarogong Kaler', 'city' => 'Kabupaten Garut', 'district' => 'Tarogong Kaler', 'latitude' => -7.217291, 'longitude' => 107.867918, 'jumlah_porsi' => 408, 'data_source' => 'database'],
        ]);
        $created++;

        // YELLOW | BULK-20260607-100
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-100',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Margahayu Perluasan 50", "address": "Jl. Margahayu Baru No.13", "district": "Margahayu", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 14}', true),
            'form2_data'          => json_decode('{"name": "Admin Y50", "email": "admin.y50@sppg.test", "password": "password123"}', true),
            'form3_data'          => json_decode('{"nutritionist": {"name": "Ahli Gizi Y50", "email": "gizi.y50@sppg.test", "password": "password123"}, "logistics_admin": {"name": "Admin Logistik Y50", "email": "logistik.y50@sppg.test", "password": "password123"}}', true),
            'latitude'            => -7.009012,
            'longitude'           => 107.532273,
            'confirmed_latitude'  => -7.010282,
            'confirmed_longitude' => 107.531431,
            'point_status'        => 'yellow',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MA Al-Azhar Cangkuang', 'npsn' => '20312171', 'level' => 'MA', 'school_status' => 'public', 'address' => 'Jl. Soekarno-Hatta No.91, Cangkuang', 'city' => 'Kabupaten Bandung', 'district' => 'Cangkuang', 'latitude' => -7.011323, 'longitude' => 107.526392, 'jumlah_porsi' => 404, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 44 Banjaran', 'npsn' => '20312701', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.16, Banjaran', 'city' => 'Kabupaten Bandung', 'district' => 'Banjaran', 'latitude' => -7.003379, 'longitude' => 107.539991, 'jumlah_porsi' => 459, 'data_source' => 'database'],
            ['school_name' => 'MA Assalam 5', 'npsn' => '20312881', 'level' => 'MA', 'school_status' => 'private', 'address' => 'Jalan Pelajar No.110, Kutawaringin', 'city' => 'Kabupaten Bandung', 'district' => 'Kutawaringin', 'latitude' => -7.012441, 'longitude' => 107.519712, 'jumlah_porsi' => 430, 'data_source' => 'database'],
        ]);
        $created++;

        // ── 50 MERAH ──────────────────────────────────────────────
        // RED | BULK-20260607-101
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-101',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Sukajadi Konflik 1", "address": "Jl. Konflik Sukajadi No.7", "district": "Sukajadi", "city": "Bandung", "province": "Jawa Barat", "capacity": 9}', true),
            'form2_data'          => json_decode('{"name": "Admin R01", "email": "admin.r01@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.891128,
            'longitude'           => 107.580979,
            'confirmed_latitude'  => -6.891487,
            'confirmed_longitude' => 107.580283,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SD Negeri 39 Cicendo', 'npsn' => '20300006', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jl. Rancabolang No.180, Cicendo', 'city' => 'Kota Bandung', 'district' => 'Cicendo', 'latitude' => -6.882571, 'longitude' => 107.594779, 'jumlah_porsi' => 384, 'data_source' => 'database'],
            ['school_name' => 'MI Negeri 9 Bojongloa Kaler', 'npsn' => '20306069', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Pajajaran No.144, Bojongloa Kaler', 'city' => 'Kota Bandung', 'district' => 'Bojongloa Kaler', 'latitude' => -6.881981, 'longitude' => 107.596672, 'jumlah_porsi' => 173, 'data_source' => 'database'],
            ['school_name' => 'SD Sukajadi 2', 'npsn' => '20300740', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jl. Raya No.19, Sukajadi', 'city' => 'Kota Bandung', 'district' => 'Sukajadi', 'latitude' => -6.873765, 'longitude' => 107.585982, 'jumlah_porsi' => 169, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-102
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-102',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Buah Batu Konflik 2", "address": "Jl. Konflik Buah Batu No.5", "district": "Buah Batu", "city": "Bandung", "province": "Jawa Barat", "capacity": 6}', true),
            'form2_data'          => json_decode('{"name": "Admin R02", "email": "admin.r02@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.950594,
            'longitude'           => 107.623107,
            'confirmed_latitude'  => -6.950164,
            'confirmed_longitude' => 107.623561,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SD Margahayu 37', 'npsn' => '20310750', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jalan Asia Afrika No.168, Margahayu', 'city' => 'Kabupaten Bandung', 'district' => 'Margahayu', 'latitude' => -6.956029, 'longitude' => 107.621154, 'jumlah_porsi' => 600, 'data_source' => 'database'],
            ['school_name' => 'SMK Negeri 27 Rancasari', 'npsn' => '20303894', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Pajajaran No.93, Rancasari', 'city' => 'Kota Bandung', 'district' => 'Rancasari', 'latitude' => -6.943371, 'longitude' => 107.619918, 'jumlah_porsi' => 1105, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-103
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-103',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Gedebage Konflik 3", "address": "Jl. Konflik Gedebage No.14", "district": "Gedebage", "city": "Bandung", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin R03", "email": "admin.r03@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.929344,
            'longitude'           => 107.680618,
            'confirmed_latitude'  => -6.929721,
            'confirmed_longitude' => 107.681217,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Cibiru 27', 'npsn' => '20304352', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Pasirkaliki No.68, Cibiru', 'city' => 'Kota Bandung', 'district' => 'Cibiru', 'latitude' => -6.928726, 'longitude' => 107.685493, 'jumlah_porsi' => 364, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-104
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-104',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Coblong Konflik 4", "address": "Jl. Konflik Coblong No.27", "district": "Coblong", "city": "Bandung", "province": "Jawa Barat", "capacity": 5}', true),
            'form2_data'          => json_decode('{"name": "Admin R04", "email": "admin.r04@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.898152,
            'longitude'           => 107.623979,
            'confirmed_latitude'  => -6.897651,
            'confirmed_longitude' => 107.623271,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MTs Negeri 42 Sukasari', 'npsn' => '20300417', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Cibadak No.36, Sukasari', 'city' => 'Kota Bandung', 'district' => 'Sukasari', 'latitude' => -6.898696, 'longitude' => 107.613341, 'jumlah_porsi' => 369, 'data_source' => 'database'],
            ['school_name' => 'SMP Negeri 45 Sukajadi', 'npsn' => '20300229', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Asia Afrika No.187, Sukajadi', 'city' => 'Kota Bandung', 'district' => 'Sukajadi', 'latitude' => -6.898794, 'longitude' => 107.607945, 'jumlah_porsi' => 376, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-105
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-105',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Regol Konflik 5", "address": "Jl. Konflik Regol No.29", "district": "Regol", "city": "Bandung", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin R05", "email": "admin.r05@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.909576,
            'longitude'           => 107.608907,
            'confirmed_latitude'  => -6.908893,
            'confirmed_longitude' => 107.609542,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MI Al-Hikmah 15', 'npsn' => '20302293', 'level' => 'MI', 'school_status' => 'public', 'address' => 'Jl. Pelajar No.17, Bandung Wetan', 'city' => 'Kota Bandung', 'district' => 'Bandung Wetan', 'latitude' => -6.914602, 'longitude' => 107.605792, 'jumlah_porsi' => 577, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-106
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-106',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Andir Konflik 6", "address": "Jl. Konflik Andir No.8", "district": "Andir", "city": "Bandung", "province": "Jawa Barat", "capacity": 8}', true),
            'form2_data'          => json_decode('{"name": "Admin R06", "email": "admin.r06@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.914513,
            'longitude'           => 107.592435,
            'confirmed_latitude'  => -6.914038,
            'confirmed_longitude' => 107.591502,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Sukasari 24', 'npsn' => '20300437', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Pajajaran No.127, Sukasari', 'city' => 'Kota Bandung', 'district' => 'Sukasari', 'latitude' => -6.907113, 'longitude' => 107.585342, 'jumlah_porsi' => 424, 'data_source' => 'database'],
            ['school_name' => 'SMKN 34 Bandung Kulon', 'npsn' => '20305595', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Pajajaran No.155, Bandung Kulon', 'city' => 'Kota Bandung', 'district' => 'Bandung Kulon', 'latitude' => -6.903773, 'longitude' => 107.595959, 'jumlah_porsi' => 434, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-107
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-107',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Bojongloa Kidul Konflik 7", "address": "Jl. Konflik Bojongloa Kidul No.22", "district": "Bojongloa Kidul", "city": "Bandung", "province": "Jawa Barat", "capacity": 8}', true),
            'form2_data'          => json_decode('{"name": "Admin R07", "email": "admin.r07@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.940619,
            'longitude'           => 107.577429,
            'confirmed_latitude'  => -6.939697,
            'confirmed_longitude' => 107.578301,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MTs Al-Falah Buah Batu', 'npsn' => '20303316', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jl. Kebon Jati No.162, Buah Batu', 'city' => 'Kota Bandung', 'district' => 'Buah Batu', 'latitude' => -6.939922, 'longitude' => 107.601035, 'jumlah_porsi' => 676, 'data_source' => 'database'],
            ['school_name' => 'SMK Negeri 7 Rancasari', 'npsn' => '20303277', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Pasirkaliki No.123, Rancasari', 'city' => 'Kota Bandung', 'district' => 'Rancasari', 'latitude' => -6.953218, 'longitude' => 107.602812, 'jumlah_porsi' => 305, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-108
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-108',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Batununggal Konflik 8", "address": "Jl. Konflik Batununggal No.21", "district": "Batununggal", "city": "Bandung", "province": "Jawa Barat", "capacity": 6}', true),
            'form2_data'          => json_decode('{"name": "Admin R08", "email": "admin.r08@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.875612,
            'longitude'           => 107.649376,
            'confirmed_latitude'  => -6.876014,
            'confirmed_longitude' => 107.649097,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Sumur Bandung 5', 'npsn' => '20301159', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Pahlawan No.90, Sumur Bandung', 'city' => 'Kota Bandung', 'district' => 'Sumur Bandung', 'latitude' => -6.889413, 'longitude' => 107.635651, 'jumlah_porsi' => 956, 'data_source' => 'database'],
            ['school_name' => 'MI Al-Hikmah 32', 'npsn' => '20302044', 'level' => 'MI', 'school_status' => 'public', 'address' => 'Jl. Pahlawan No.168, Lengkong', 'city' => 'Kota Bandung', 'district' => 'Lengkong', 'latitude' => -6.899858, 'longitude' => 107.63638, 'jumlah_porsi' => 209, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-109
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-109',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Margacinta Konflik 9", "address": "Jl. Konflik Margacinta No.11", "district": "Margacinta", "city": "Bandung", "province": "Jawa Barat", "capacity": 6}', true),
            'form2_data'          => json_decode('{"name": "Admin R09", "email": "admin.r09@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.954883,
            'longitude'           => 107.660184,
            'confirmed_latitude'  => -6.955853,
            'confirmed_longitude' => 107.660773,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMKN 12 Margacinta', 'npsn' => '20303244', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Asia Afrika No.33, Margacinta', 'city' => 'Kota Bandung', 'district' => 'Margacinta', 'latitude' => -6.962199, 'longitude' => 107.645827, 'jumlah_porsi' => 1333, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-110
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-110',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Arcamanik Konflik 10", "address": "Jl. Konflik Arcamanik No.19", "district": "Arcamanik", "city": "Bandung", "province": "Jawa Barat", "capacity": 8}', true),
            'form2_data'          => json_decode('{"name": "Admin R10", "email": "admin.r10@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.930352,
            'longitude'           => 107.650844,
            'confirmed_latitude'  => -6.929785,
            'confirmed_longitude' => 107.650795,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MA Negeri 32 Cibiru', 'npsn' => '20304309', 'level' => 'MA', 'school_status' => 'private', 'address' => 'Jl. Pajajaran No.152, Cibiru', 'city' => 'Kota Bandung', 'district' => 'Cibiru', 'latitude' => -6.929081, 'longitude' => 107.65324, 'jumlah_porsi' => 550, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-111
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-111',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Cimahi Utara Konflik 11", "address": "Jl. Konflik Cimahi Utara No.16", "district": "Cimahi Utara", "city": "Cimahi", "province": "Jawa Barat", "capacity": 6}', true),
            'form2_data'          => json_decode('{"name": "Admin R11", "email": "admin.r11@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.87572,
            'longitude'           => 107.52666,
            'confirmed_latitude'  => -6.876537,
            'confirmed_longitude' => 107.526897,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMPN 4 Cimahi Selatan', 'npsn' => '20306675', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Ahmad Yani No.25, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.871867, 'longitude' => 107.533824, 'jumlah_porsi' => 358, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-112
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-112',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Cimahi Tengah Konflik 12", "address": "Jl. Konflik Cimahi Tengah No.21", "district": "Cimahi Tengah", "city": "Cimahi", "province": "Jawa Barat", "capacity": 5}', true),
            'form2_data'          => json_decode('{"name": "Admin R12", "email": "admin.r12@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.894456,
            'longitude'           => 107.560523,
            'confirmed_latitude'  => -6.895148,
            'confirmed_longitude' => 107.560596,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMP Negeri 8 Cimahi Utara', 'npsn' => '20307805', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Pasirkaliki No.133, Cimahi Utara', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Utara', 'latitude' => -6.881109, 'longitude' => 107.543358, 'jumlah_porsi' => 368, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-113
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-113',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Cimahi Selatan Konflik 13", "address": "Jl. Konflik Cimahi Selatan No.26", "district": "Cimahi Selatan", "city": "Cimahi", "province": "Jawa Barat", "capacity": 10}', true),
            'form2_data'          => json_decode('{"name": "Admin R13", "email": "admin.r13@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.897271,
            'longitude'           => 107.544731,
            'confirmed_latitude'  => -6.896487,
            'confirmed_longitude' => 107.545197,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Cimahi Selatan 37', 'npsn' => '20307271', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Gatot Subroto No.110, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.901942, 'longitude' => 107.531986, 'jumlah_porsi' => 1368, 'data_source' => 'database'],
            ['school_name' => 'SD Negeri 24 Cimahi Selatan', 'npsn' => '20307304', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Setiabudhi No.121, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.899674, 'longitude' => 107.533841, 'jumlah_porsi' => 482, 'data_source' => 'database'],
            ['school_name' => 'MI Al-Hikmah 10', 'npsn' => '20306692', 'level' => 'MI', 'school_status' => 'private', 'address' => 'Jl. Kebon Jati No.170, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.894382, 'longitude' => 107.531823, 'jumlah_porsi' => 501, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-114
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-114',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Padalarang Konflik 14", "address": "Jl. Konflik Padalarang No.16", "district": "Padalarang", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 7}', true),
            'form2_data'          => json_decode('{"name": "Admin R14", "email": "admin.r14@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.833637,
            'longitude'           => 107.475171,
            'confirmed_latitude'  => -6.833224,
            'confirmed_longitude' => 107.475289,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMKN 9 Padalarang', 'npsn' => '20308115', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Cihampelas No.120, Padalarang', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Padalarang', 'latitude' => -6.830264, 'longitude' => 107.461535, 'jumlah_porsi' => 1329, 'data_source' => 'database'],
            ['school_name' => 'MTs Nurul Islam 49', 'npsn' => '20308401', 'level' => 'MTs', 'school_status' => 'private', 'address' => 'Jalan Pajajaran No.120, Cikalongwetan', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Cikalongwetan', 'latitude' => -6.840067, 'longitude' => 107.459577, 'jumlah_porsi' => 123, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-115
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-115',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Lembang Konflik 15", "address": "Jl. Konflik Lembang No.11", "district": "Lembang", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 7}', true),
            'form2_data'          => json_decode('{"name": "Admin R15", "email": "admin.r15@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.813294,
            'longitude'           => 107.602944,
            'confirmed_latitude'  => -6.812829,
            'confirmed_longitude' => 107.602495,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMKN 4 Parongpong', 'npsn' => '20310415', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Gedebage No.155, Parongpong', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Parongpong', 'latitude' => -6.804437, 'longitude' => 107.630339, 'jumlah_porsi' => 585, 'data_source' => 'database'],
            ['school_name' => 'SMP Negeri 37 Lembang', 'npsn' => '20309701', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Asia Afrika No.130, Lembang', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Lembang', 'latitude' => -6.8261, 'longitude' => 107.611996, 'jumlah_porsi' => 268, 'data_source' => 'database'],
            ['school_name' => 'SMPN 44 Ngamprah', 'npsn' => '20309777', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Setiabudhi No.66, Ngamprah', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Ngamprah', 'latitude' => -6.792894, 'longitude' => 107.623381, 'jumlah_porsi' => 692, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-116
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-116',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Batujajar Konflik 16", "address": "Jl. Konflik Batujajar No.15", "district": "Batujajar", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 7}', true),
            'form2_data'          => json_decode('{"name": "Admin R16", "email": "admin.r16@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.838165,
            'longitude'           => 107.520261,
            'confirmed_latitude'  => -6.838574,
            'confirmed_longitude' => 107.519788,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Negeri 19 Cipatat', 'npsn' => '20309006', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jalan Setiabudhi No.89, Cipatat', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Cipatat', 'latitude' => -6.815427, 'longitude' => 107.486584, 'jumlah_porsi' => 794, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-117
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-117',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Ngamprah Konflik 17", "address": "Jl. Konflik Ngamprah No.29", "district": "Ngamprah", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 11}', true),
            'form2_data'          => json_decode('{"name": "Admin R17", "email": "admin.r17@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.852986,
            'longitude'           => 107.548163,
            'confirmed_latitude'  => -6.853949,
            'confirmed_longitude' => 107.548877,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMPN 22 Cimahi Selatan', 'npsn' => '20307493', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Soekarno-Hatta No.112, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.853947, 'longitude' => 107.553247, 'jumlah_porsi' => 495, 'data_source' => 'database'],
            ['school_name' => 'MA Assalam 7', 'npsn' => '20306753', 'level' => 'MA', 'school_status' => 'public', 'address' => 'Jl. Gedebage No.87, Cimahi Tengah', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Tengah', 'latitude' => -6.854654, 'longitude' => 107.546426, 'jumlah_porsi' => 485, 'data_source' => 'database'],
            ['school_name' => 'MTs Al-Falah Cimahi Utara', 'npsn' => '20307771', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jalan Soekarno-Hatta No.114, Cimahi Utara', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Utara', 'latitude' => -6.860779, 'longitude' => 107.544025, 'jumlah_porsi' => 642, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-118
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-118',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Parongpong Konflik 18", "address": "Jl. Konflik Parongpong No.7", "district": "Parongpong", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 6}', true),
            'form2_data'          => json_decode('{"name": "Admin R18", "email": "admin.r18@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.797336,
            'longitude'           => 107.581219,
            'confirmed_latitude'  => -6.797288,
            'confirmed_longitude' => 107.581214,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SD Negeri 35 Parongpong', 'npsn' => '20309256', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.46, Parongpong', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Parongpong', 'latitude' => -6.786545, 'longitude' => 107.584759, 'jumlah_porsi' => 506, 'data_source' => 'database'],
            ['school_name' => 'SMK Parongpong 35', 'npsn' => '20310059', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Pahlawan No.129, Parongpong', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Parongpong', 'latitude' => -6.783325, 'longitude' => 107.595528, 'jumlah_porsi' => 1379, 'data_source' => 'database'],
            ['school_name' => 'SMPN 29 Cisarua', 'npsn' => '20309096', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jalan Pelajar No.52, Cisarua', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Cisarua', 'latitude' => -6.792273, 'longitude' => 107.597541, 'jumlah_porsi' => 675, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-119
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-119',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Dayeuhkolot Konflik 19", "address": "Jl. Konflik Dayeuhkolot No.4", "district": "Dayeuhkolot", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 10}', true),
            'form2_data'          => json_decode('{"name": "Admin R19", "email": "admin.r19@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.98472,
            'longitude'           => 107.620812,
            'confirmed_latitude'  => -6.985527,
            'confirmed_longitude' => 107.62113,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MA Al-Azhar Baleendah', 'npsn' => '20311507', 'level' => 'MA', 'school_status' => 'public', 'address' => 'Jl. Setiabudi No.173, Baleendah', 'city' => 'Kabupaten Bandung', 'district' => 'Baleendah', 'latitude' => -6.980038, 'longitude' => 107.637261, 'jumlah_porsi' => 347, 'data_source' => 'database'],
            ['school_name' => 'SMK Teknologi Dayeuhkolot', 'npsn' => '20311035', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Setiabudhi No.142, Dayeuhkolot', 'city' => 'Kabupaten Bandung', 'district' => 'Dayeuhkolot', 'latitude' => -6.981297, 'longitude' => 107.637447, 'jumlah_porsi' => 550, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-120
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-120',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Baleendah Konflik 20", "address": "Jl. Konflik Baleendah No.4", "district": "Baleendah", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 7}', true),
            'form2_data'          => json_decode('{"name": "Admin R20", "email": "admin.r20@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -7.004218,
            'longitude'           => 107.640644,
            'confirmed_latitude'  => -7.004247,
            'confirmed_longitude' => 107.639842,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Baleendah 31', 'npsn' => '20310511', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Gatot Subroto No.4, Baleendah', 'city' => 'Kabupaten Bandung', 'district' => 'Baleendah', 'latitude' => -6.995578, 'longitude' => 107.613119, 'jumlah_porsi' => 440, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-121
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-121',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Soreang Konflik 21", "address": "Jl. Konflik Soreang No.2", "district": "Soreang", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin R21", "email": "admin.r21@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -7.043319,
            'longitude'           => 107.502477,
            'confirmed_latitude'  => -7.043586,
            'confirmed_longitude' => 107.502689,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Katapang 34', 'npsn' => '20311997', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Ahmad Yani No.144, Katapang', 'city' => 'Kabupaten Bandung', 'district' => 'Katapang', 'latitude' => -7.03398, 'longitude' => 107.517933, 'jumlah_porsi' => 1029, 'data_source' => 'database'],
            ['school_name' => 'SMA Soreang 32', 'npsn' => '20312337', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Setiabudhi No.144, Soreang', 'city' => 'Kabupaten Bandung', 'district' => 'Soreang', 'latitude' => -7.029706, 'longitude' => 107.519726, 'jumlah_porsi' => 1207, 'data_source' => 'database'],
            ['school_name' => 'SMK Negeri 6 Katapang', 'npsn' => '20312396', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Gatot Subroto No.9, Katapang', 'city' => 'Kabupaten Bandung', 'district' => 'Katapang', 'latitude' => -7.04217, 'longitude' => 107.522048, 'jumlah_porsi' => 544, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-122
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-122',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Katapang Konflik 22", "address": "Jl. Konflik Katapang No.30", "district": "Katapang", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 8}', true),
            'form2_data'          => json_decode('{"name": "Admin R22", "email": "admin.r22@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -7.000894,
            'longitude'           => 107.579221,
            'confirmed_latitude'  => -7.000114,
            'confirmed_longitude' => 107.578381,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Negeri 44 Banjaran', 'npsn' => '20312701', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.16, Banjaran', 'city' => 'Kabupaten Bandung', 'district' => 'Banjaran', 'latitude' => -7.003379, 'longitude' => 107.539991, 'jumlah_porsi' => 459, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 36 Cangkuang', 'npsn' => '20312493', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Cibadak No.199, Cangkuang', 'city' => 'Kabupaten Bandung', 'district' => 'Cangkuang', 'latitude' => -7.02767, 'longitude' => 107.546736, 'jumlah_porsi' => 1310, 'data_source' => 'database'],
            ['school_name' => 'SMK Teknologi Cangkuang', 'npsn' => '20311577', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jalan Kebon Jati No.103, Cangkuang', 'city' => 'Kabupaten Bandung', 'district' => 'Cangkuang', 'latitude' => -7.028142, 'longitude' => 107.543065, 'jumlah_porsi' => 1056, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-123
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-123',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Margaasih Konflik 23", "address": "Jl. Konflik Margaasih No.27", "district": "Margaasih", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 11}', true),
            'form2_data'          => json_decode('{"name": "Admin R23", "email": "admin.r23@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.973707,
            'longitude'           => 107.59124,
            'confirmed_latitude'  => -6.973473,
            'confirmed_longitude' => 107.591298,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Negeri 49 Rancasari', 'npsn' => '20303796', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Sudirman No.81, Rancasari', 'city' => 'Kota Bandung', 'district' => 'Rancasari', 'latitude' => -6.963384, 'longitude' => 107.599085, 'jumlah_porsi' => 1027, 'data_source' => 'database'],
            ['school_name' => 'SMA Buah Batu 49', 'npsn' => '20303366', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Cibadak No.170, Buah Batu', 'city' => 'Kota Bandung', 'district' => 'Buah Batu', 'latitude' => -6.972639, 'longitude' => 107.605835, 'jumlah_porsi' => 1330, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-124
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-124',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Banjaran Konflik 24", "address": "Jl. Konflik Banjaran No.4", "district": "Banjaran", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 6}', true),
            'form2_data'          => json_decode('{"name": "Admin R24", "email": "admin.r24@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -7.079148,
            'longitude'           => 107.588091,
            'confirmed_latitude'  => -7.078698,
            'confirmed_longitude' => 107.588154,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMAN 4 Katapang', 'npsn' => '20312611', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Buah Batu No.138, Katapang', 'city' => 'Kabupaten Bandung', 'district' => 'Katapang', 'latitude' => -7.047926, 'longitude' => 107.546088, 'jumlah_porsi' => 799, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 21 Cangkuang', 'npsn' => '20311916', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jalan Merdeka No.182, Cangkuang', 'city' => 'Kabupaten Bandung', 'district' => 'Cangkuang', 'latitude' => -7.050806, 'longitude' => 107.539018, 'jumlah_porsi' => 423, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-125
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-125',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Bojongsoang Konflik 25", "address": "Jl. Konflik Bojongsoang No.18", "district": "Bojongsoang", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 8}', true),
            'form2_data'          => json_decode('{"name": "Admin R25", "email": "admin.r25@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.982807,
            'longitude'           => 107.679912,
            'confirmed_latitude'  => -6.982309,
            'confirmed_longitude' => 107.679742,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MI Negeri 22 Dayeuhkolot', 'npsn' => '20311143', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jalan Raya No.163, Dayeuhkolot', 'city' => 'Kabupaten Bandung', 'district' => 'Dayeuhkolot', 'latitude' => -6.992253, 'longitude' => 107.656825, 'jumlah_porsi' => 602, 'data_source' => 'database'],
            ['school_name' => 'MI Negeri 48 Baleendah', 'npsn' => '20311225', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jalan Soekarno-Hatta No.28, Baleendah', 'city' => 'Kabupaten Bandung', 'district' => 'Baleendah', 'latitude' => -7.00208, 'longitude' => 107.654123, 'jumlah_porsi' => 555, 'data_source' => 'database'],
            ['school_name' => 'MTs Negeri 40 Bojongsoang', 'npsn' => '20311396', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jalan Cihampelas No.86, Bojongsoang', 'city' => 'Kabupaten Bandung', 'district' => 'Bojongsoang', 'latitude' => -6.98375, 'longitude' => 107.647619, 'jumlah_porsi' => 239, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-126
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-126',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Sumedang Utara Konflik 26", "address": "Jl. Konflik Sumedang Utara No.23", "district": "Sumedang Utara", "city": "Kabupaten Sumedang", "province": "Jawa Barat", "capacity": 10}', true),
            'form2_data'          => json_decode('{"name": "Admin R26", "email": "admin.r26@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.838547,
            'longitude'           => 107.930193,
            'confirmed_latitude'  => -6.838217,
            'confirmed_longitude' => 107.930959,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SDN 13 Jatinangor', 'npsn' => '20313883', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jalan Buah Batu No.63, Jatinangor', 'city' => 'Kabupaten Sumedang', 'district' => 'Jatinangor', 'latitude' => -6.85195, 'longitude' => 107.924992, 'jumlah_porsi' => 605, 'data_source' => 'database'],
            ['school_name' => 'SMP Sumedang Selatan 25', 'npsn' => '20313037', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Sudirman No.136, Sumedang Selatan', 'city' => 'Kabupaten Sumedang', 'district' => 'Sumedang Selatan', 'latitude' => -6.846579, 'longitude' => 107.926006, 'jumlah_porsi' => 268, 'data_source' => 'database'],
            ['school_name' => 'SMK Sumedang Selatan 4', 'npsn' => '20313499', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.30, Sumedang Selatan', 'city' => 'Kabupaten Sumedang', 'district' => 'Sumedang Selatan', 'latitude' => -6.85252, 'longitude' => 107.925925, 'jumlah_porsi' => 833, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-127
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-127',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Jatinangor Konflik 27", "address": "Jl. Konflik Jatinangor No.22", "district": "Jatinangor", "city": "Kabupaten Sumedang", "province": "Jawa Barat", "capacity": 11}', true),
            'form2_data'          => json_decode('{"name": "Admin R27", "email": "admin.r27@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.86456,
            'longitude'           => 107.893157,
            'confirmed_latitude'  => -6.865244,
            'confirmed_longitude' => 107.894154,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MTs Negeri 34 Sumedang Selatan', 'npsn' => '20314100', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jalan Cihampelas No.192, Sumedang Selatan', 'city' => 'Kabupaten Sumedang', 'district' => 'Sumedang Selatan', 'latitude' => -6.872438, 'longitude' => 107.897982, 'jumlah_porsi' => 236, 'data_source' => 'database'],
            ['school_name' => 'MTs Negeri 27 Sumedang Selatan', 'npsn' => '20313082', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Sudirman No.26, Sumedang Selatan', 'city' => 'Kabupaten Sumedang', 'district' => 'Sumedang Selatan', 'latitude' => -6.869459, 'longitude' => 107.899484, 'jumlah_porsi' => 493, 'data_source' => 'database'],
            ['school_name' => 'SMP Negeri 47 Jatinangor', 'npsn' => '20313602', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jl. Ahmad Yani No.110, Jatinangor', 'city' => 'Kabupaten Sumedang', 'district' => 'Jatinangor', 'latitude' => -6.862325, 'longitude' => 107.896256, 'jumlah_porsi' => 690, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-128
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-128',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Sumedang Selatan Konflik 28", "address": "Jl. Konflik Sumedang Selatan No.28", "district": "Sumedang Selatan", "city": "Kabupaten Sumedang", "province": "Jawa Barat", "capacity": 10}', true),
            'form2_data'          => json_decode('{"name": "Admin R28", "email": "admin.r28@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.826606,
            'longitude'           => 107.940864,
            'confirmed_latitude'  => -6.826863,
            'confirmed_longitude' => 107.941392,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SD Jatinangor 7', 'npsn' => '20314018', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.126, Jatinangor', 'city' => 'Kabupaten Sumedang', 'district' => 'Jatinangor', 'latitude' => -6.825071, 'longitude' => 107.945727, 'jumlah_porsi' => 245, 'data_source' => 'database'],
            ['school_name' => 'SMK Sumedang Utara 28', 'npsn' => '20314190', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Gatot Subroto No.67, Sumedang Utara', 'city' => 'Kabupaten Sumedang', 'district' => 'Sumedang Utara', 'latitude' => -6.831032, 'longitude' => 107.943195, 'jumlah_porsi' => 1004, 'data_source' => 'database'],
            ['school_name' => 'SD Sumedang Utara 12', 'npsn' => '20313784', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Setiabudhi No.18, Sumedang Utara', 'city' => 'Kabupaten Sumedang', 'district' => 'Sumedang Utara', 'latitude' => -6.829622, 'longitude' => 107.939928, 'jumlah_porsi' => 418, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-129
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-129',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Tarogong Kidul Konflik 29", "address": "Jl. Konflik Tarogong Kidul No.13", "district": "Tarogong Kidul", "city": "Kabupaten Garut", "province": "Jawa Barat", "capacity": 8}', true),
            'form2_data'          => json_decode('{"name": "Admin R29", "email": "admin.r29@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -7.207781,
            'longitude'           => 107.869387,
            'confirmed_latitude'  => -7.20725,
            'confirmed_longitude' => 107.870239,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Negeri 46 Banyuresmi', 'npsn' => '20314902', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Rancabolang No.43, Banyuresmi', 'city' => 'Kabupaten Garut', 'district' => 'Banyuresmi', 'latitude' => -7.214199, 'longitude' => 107.886415, 'jumlah_porsi' => 309, 'data_source' => 'database'],
            ['school_name' => 'SD Negeri 17 Garut Kota', 'npsn' => '20314842', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Merdeka No.99, Garut Kota', 'city' => 'Kabupaten Garut', 'district' => 'Garut Kota', 'latitude' => -7.223339, 'longitude' => 107.885622, 'jumlah_porsi' => 270, 'data_source' => 'database'],
            ['school_name' => 'SMP Garut Kota 21', 'npsn' => '20314789', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jl. Gedebage No.164, Garut Kota', 'city' => 'Kabupaten Garut', 'district' => 'Garut Kota', 'latitude' => -7.207606, 'longitude' => 107.881051, 'jumlah_porsi' => 111, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-130
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-130',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Garut Kota Konflik 30", "address": "Jl. Konflik Garut Kota No.24", "district": "Garut Kota", "city": "Kabupaten Garut", "province": "Jawa Barat", "capacity": 12}', true),
            'form2_data'          => json_decode('{"name": "Admin R30", "email": "admin.r30@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -7.213245,
            'longitude'           => 107.909436,
            'confirmed_latitude'  => -7.214176,
            'confirmed_longitude' => 107.909005,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MTs Nurul Islam 18', 'npsn' => '20314431', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jl. Ahmad Yani No.153, Banyuresmi', 'city' => 'Kabupaten Garut', 'district' => 'Banyuresmi', 'latitude' => -7.235188, 'longitude' => 107.911564, 'jumlah_porsi' => 387, 'data_source' => 'database'],
            ['school_name' => 'MTs Negeri 47 Tarogong Kaler', 'npsn' => '20314685', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jalan Rancabolang No.82, Tarogong Kaler', 'city' => 'Kabupaten Garut', 'district' => 'Tarogong Kaler', 'latitude' => -7.237611, 'longitude' => 107.91554, 'jumlah_porsi' => 465, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-131
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-131',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Cianjur Konflik 31", "address": "Jl. Konflik Cianjur No.8", "district": "Cianjur", "city": "Kabupaten Cianjur", "province": "Jawa Barat", "capacity": 9}', true),
            'form2_data'          => json_decode('{"name": "Admin R31", "email": "admin.r31@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.81745,
            'longitude'           => 107.14339,
            'confirmed_latitude'  => -6.81802,
            'confirmed_longitude' => 107.14302,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Teknologi Cianjur', 'npsn' => '20315338', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.190, Cianjur', 'city' => 'Kabupaten Cianjur', 'district' => 'Cianjur', 'latitude' => -6.82084, 'longitude' => 107.140898, 'jumlah_porsi' => 965, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-132
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-132',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Cipanas Konflik 32", "address": "Jl. Konflik Cipanas No.4", "district": "Cipanas", "city": "Kabupaten Cianjur", "province": "Jawa Barat", "capacity": 9}', true),
            'form2_data'          => json_decode('{"name": "Admin R32", "email": "admin.r32@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.801204,
            'longitude'           => 107.082905,
            'confirmed_latitude'  => -6.802129,
            'confirmed_longitude' => 107.082616,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SD Cugenang 15', 'npsn' => '20315532', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jl. Cibadak No.167, Cugenang', 'city' => 'Kabupaten Cianjur', 'district' => 'Cugenang', 'latitude' => -6.824892, 'longitude' => 107.113872, 'jumlah_porsi' => 109, 'data_source' => 'database'],
            ['school_name' => 'MI Negeri 12 Cipanas', 'npsn' => '20315471', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jalan Soekarno-Hatta No.42, Cipanas', 'city' => 'Kabupaten Cianjur', 'district' => 'Cipanas', 'latitude' => -6.801748, 'longitude' => 107.126862, 'jumlah_porsi' => 192, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-133
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-133',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Warudoyong Konflik 33", "address": "Jl. Konflik Warudoyong No.26", "district": "Warudoyong", "city": "Kota Sukabumi", "province": "Jawa Barat", "capacity": 8}', true),
            'form2_data'          => json_decode('{"name": "Admin R33", "email": "admin.r33@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.917376,
            'longitude'           => 106.934484,
            'confirmed_latitude'  => -6.917775,
            'confirmed_longitude' => 106.934625,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMKN 43 Cikole', 'npsn' => '20316850', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.170, Cikole', 'city' => 'Kota Sukabumi', 'district' => 'Cikole', 'latitude' => -6.917749, 'longitude' => 106.929016, 'jumlah_porsi' => 988, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-134
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-134',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Cikole Konflik 34", "address": "Jl. Konflik Cikole No.26", "district": "Cikole", "city": "Kota Sukabumi", "province": "Jawa Barat", "capacity": 6}', true),
            'form2_data'          => json_decode('{"name": "Admin R34", "email": "admin.r34@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.947948,
            'longitude'           => 106.933591,
            'confirmed_latitude'  => -6.947983,
            'confirmed_longitude' => 106.933568,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMP Warudoyong 26', 'npsn' => '20317148', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jalan Pelajar No.184, Warudoyong', 'city' => 'Kota Sukabumi', 'district' => 'Warudoyong', 'latitude' => -6.93876, 'longitude' => 106.944002, 'jumlah_porsi' => 147, 'data_source' => 'database'],
            ['school_name' => 'SD Negeri 14 Cikole', 'npsn' => '20316558', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jalan Setiabudi No.170, Cikole', 'city' => 'Kota Sukabumi', 'district' => 'Cikole', 'latitude' => -6.951569, 'longitude' => 106.931106, 'jumlah_porsi' => 240, 'data_source' => 'database'],
            ['school_name' => 'SMK Teknologi Cikole', 'npsn' => '20316828', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jalan Kebon Jati No.174, Cikole', 'city' => 'Kota Sukabumi', 'district' => 'Cikole', 'latitude' => -6.933343, 'longitude' => 106.936062, 'jumlah_porsi' => 934, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-135
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-135',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Cidadap Konflik 35", "address": "Jl. Konflik Cidadap No.2", "district": "Cidadap", "city": "Bandung", "province": "Jawa Barat", "capacity": 6}', true),
            'form2_data'          => json_decode('{"name": "Admin R35", "email": "admin.r35@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.874859,
            'longitude'           => 107.633493,
            'confirmed_latitude'  => -6.874397,
            'confirmed_longitude' => 107.633646,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMKN 41 Coblong', 'npsn' => '20300505', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Pahlawan No.96, Coblong', 'city' => 'Kota Bandung', 'district' => 'Coblong', 'latitude' => -6.87081, 'longitude' => 107.612144, 'jumlah_porsi' => 1286, 'data_source' => 'database'],
            ['school_name' => 'SMA Sukajadi 16', 'npsn' => '20300005', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Diponegoro No.174, Sukajadi', 'city' => 'Kota Bandung', 'district' => 'Sukajadi', 'latitude' => -6.871991, 'longitude' => 107.61247, 'jumlah_porsi' => 1064, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 39 Sukasari', 'npsn' => '20300439', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Setiabudhi No.153, Sukasari', 'city' => 'Kota Bandung', 'district' => 'Sukasari', 'latitude' => -6.858183, 'longitude' => 107.611585, 'jumlah_porsi' => 1333, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-136
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-136',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Cicendo Konflik 36", "address": "Jl. Konflik Cicendo No.14", "district": "Cicendo", "city": "Bandung", "province": "Jawa Barat", "capacity": 10}', true),
            'form2_data'          => json_decode('{"name": "Admin R36", "email": "admin.r36@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.880196,
            'longitude'           => 107.578381,
            'confirmed_latitude'  => -6.879336,
            'confirmed_longitude' => 107.577622,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Teknologi Bojongloa Kaler', 'npsn' => '20305636', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.87, Bojongloa Kaler', 'city' => 'Kota Bandung', 'district' => 'Bojongloa Kaler', 'latitude' => -6.882302, 'longitude' => 107.574486, 'jumlah_porsi' => 394, 'data_source' => 'database'],
            ['school_name' => 'SMA Bojongloa Kidul 4', 'npsn' => '20305388', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jalan Pajajaran No.30, Bojongloa Kidul', 'city' => 'Kota Bandung', 'district' => 'Bojongloa Kidul', 'latitude' => -6.886721, 'longitude' => 107.578707, 'jumlah_porsi' => 941, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-137
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-137',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Lengkong Konflik 37", "address": "Jl. Konflik Lengkong No.2", "district": "Lengkong", "city": "Bandung", "province": "Jawa Barat", "capacity": 5}', true),
            'form2_data'          => json_decode('{"name": "Admin R37", "email": "admin.r37@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.932378,
            'longitude'           => 107.637255,
            'confirmed_latitude'  => -6.932768,
            'confirmed_longitude' => 107.636337,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MI Al-Hikmah 25', 'npsn' => '20302555', 'level' => 'MI', 'school_status' => 'public', 'address' => 'Jl. Cihampelas No.84, Regol', 'city' => 'Kota Bandung', 'district' => 'Regol', 'latitude' => -6.918784, 'longitude' => 107.635671, 'jumlah_porsi' => 384, 'data_source' => 'database'],
            ['school_name' => 'MI Islamiyah Cidadap', 'npsn' => '20300814', 'level' => 'MI', 'school_status' => 'public', 'address' => 'Jalan Pelajar No.139, Cidadap', 'city' => 'Kota Bandung', 'district' => 'Cidadap', 'latitude' => -6.904875, 'longitude' => 107.633712, 'jumlah_porsi' => 684, 'data_source' => 'database'],
            ['school_name' => 'SMA Negeri 22 Batununggal', 'npsn' => '20301531', 'level' => 'SMA', 'school_status' => 'public', 'address' => 'Jl. Cibadak No.46, Batununggal', 'city' => 'Kota Bandung', 'district' => 'Batununggal', 'latitude' => -6.905644, 'longitude' => 107.641329, 'jumlah_porsi' => 278, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-138
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-138',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Antapani Konflik 38", "address": "Jl. Konflik Antapani No.22", "district": "Antapani", "city": "Bandung", "province": "Jawa Barat", "capacity": 8}', true),
            'form2_data'          => json_decode('{"name": "Admin R38", "email": "admin.r38@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.906407,
            'longitude'           => 107.64447,
            'confirmed_latitude'  => -6.905944,
            'confirmed_longitude' => 107.644256,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MI Negeri 20 Gedebage', 'npsn' => '20304804', 'level' => 'SD', 'school_status' => 'private', 'address' => 'Jl. Kebon Jati No.94, Gedebage', 'city' => 'Kota Bandung', 'district' => 'Gedebage', 'latitude' => -6.90744, 'longitude' => 107.652682, 'jumlah_porsi' => 402, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-139
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-139',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Rancasari Konflik 39", "address": "Jl. Konflik Rancasari No.10", "district": "Rancasari", "city": "Bandung", "province": "Jawa Barat", "capacity": 6}', true),
            'form2_data'          => json_decode('{"name": "Admin R39", "email": "admin.r39@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.965142,
            'longitude'           => 107.646238,
            'confirmed_latitude'  => -6.964569,
            'confirmed_longitude' => 107.645534,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMKN 12 Margacinta', 'npsn' => '20303244', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Asia Afrika No.33, Margacinta', 'city' => 'Kota Bandung', 'district' => 'Margacinta', 'latitude' => -6.962199, 'longitude' => 107.645827, 'jumlah_porsi' => 1333, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-140
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-140',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Bandung Kulon Konflik 40", "address": "Jl. Konflik Bandung Kulon No.9", "district": "Bandung Kulon", "city": "Bandung", "province": "Jawa Barat", "capacity": 5}', true),
            'form2_data'          => json_decode('{"name": "Admin R40", "email": "admin.r40@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.944601,
            'longitude'           => 107.5787,
            'confirmed_latitude'  => -6.945431,
            'confirmed_longitude' => 107.578704,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Negeri 49 Rancasari', 'npsn' => '20303796', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Sudirman No.81, Rancasari', 'city' => 'Kota Bandung', 'district' => 'Rancasari', 'latitude' => -6.963384, 'longitude' => 107.599085, 'jumlah_porsi' => 1027, 'data_source' => 'database'],
            ['school_name' => 'SMK Negeri 7 Rancasari', 'npsn' => '20303277', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Pasirkaliki No.123, Rancasari', 'city' => 'Kota Bandung', 'district' => 'Rancasari', 'latitude' => -6.953218, 'longitude' => 107.602812, 'jumlah_porsi' => 305, 'data_source' => 'database'],
            ['school_name' => 'MTs Al-Falah Buah Batu', 'npsn' => '20303316', 'level' => 'MTs', 'school_status' => 'public', 'address' => 'Jl. Kebon Jati No.162, Buah Batu', 'city' => 'Kota Bandung', 'district' => 'Buah Batu', 'latitude' => -6.939922, 'longitude' => 107.601035, 'jumlah_porsi' => 676, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-141
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-141',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Sukasari Konflik 41", "address": "Jl. Konflik Sukasari No.16", "district": "Sukasari", "city": "Bandung", "province": "Jawa Barat", "capacity": 7}', true),
            'form2_data'          => json_decode('{"name": "Admin R41", "email": "admin.r41@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.880228,
            'longitude'           => 107.565794,
            'confirmed_latitude'  => -6.880437,
            'confirmed_longitude' => 107.565937,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MI Negeri 11 Bojongloa Kidul', 'npsn' => '20306175', 'level' => 'SD', 'school_status' => 'public', 'address' => 'Jalan Sudirman No.112, Bojongloa Kidul', 'city' => 'Kota Bandung', 'district' => 'Bojongloa Kidul', 'latitude' => -6.881379, 'longitude' => 107.566024, 'jumlah_porsi' => 510, 'data_source' => 'database'],
            ['school_name' => 'SMP Cimahi Tengah 15', 'npsn' => '20306892', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jalan Pasirkaliki No.50, Cimahi Tengah', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Tengah', 'latitude' => -6.882144, 'longitude' => 107.564344, 'jumlah_porsi' => 551, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-142
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-142',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Babakan Ciparay Konflik 42", "address": "Jl. Konflik Babakan Ciparay No.9", "district": "Babakan Ciparay", "city": "Bandung", "province": "Jawa Barat", "capacity": 8}', true),
            'form2_data'          => json_decode('{"name": "Admin R42", "email": "admin.r42@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.938423,
            'longitude'           => 107.554021,
            'confirmed_latitude'  => -6.937869,
            'confirmed_longitude' => 107.553668,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMP Negeri 5 Babakan Ciparay', 'npsn' => '20305511', 'level' => 'SMP', 'school_status' => 'public', 'address' => 'Jalan Buah Batu No.38, Babakan Ciparay', 'city' => 'Kota Bandung', 'district' => 'Babakan Ciparay', 'latitude' => -6.923477, 'longitude' => 107.56775, 'jumlah_porsi' => 699, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-143
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-143',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Buah Batu Konflik 43", "address": "Jl. Konflik Buah Batu No.12", "district": "Buah Batu", "city": "Bandung", "province": "Jawa Barat", "capacity": 11}', true),
            'form2_data'          => json_decode('{"name": "Admin R43", "email": "admin.r43@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.942055,
            'longitude'           => 107.639009,
            'confirmed_latitude'  => -6.942674,
            'confirmed_longitude' => 107.638698,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MA Negeri 31 Buah Batu', 'npsn' => '20302652', 'level' => 'MA', 'school_status' => 'public', 'address' => 'Jl. Soekarno-Hatta No.58, Buah Batu', 'city' => 'Kota Bandung', 'district' => 'Buah Batu', 'latitude' => -6.941884, 'longitude' => 107.635314, 'jumlah_porsi' => 141, 'data_source' => 'database'],
            ['school_name' => 'SMPN 31 Buah Batu', 'npsn' => '20303730', 'level' => 'SMP', 'school_status' => 'private', 'address' => 'Jalan Ahmad Yani No.83, Buah Batu', 'city' => 'Kota Bandung', 'district' => 'Buah Batu', 'latitude' => -6.940804, 'longitude' => 107.63948, 'jumlah_porsi' => 437, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-144
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-144',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Ujungberung Konflik 44", "address": "Jl. Konflik Ujungberung No.20", "district": "Ujungberung", "city": "Bandung", "province": "Jawa Barat", "capacity": 7}', true),
            'form2_data'          => json_decode('{"name": "Admin R44", "email": "admin.r44@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.924878,
            'longitude'           => 107.659871,
            'confirmed_latitude'  => -6.925586,
            'confirmed_longitude' => 107.658961,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MI Nurul Huda Arcamanik 5', 'npsn' => '20304947', 'level' => 'MI', 'school_status' => 'private', 'address' => 'Jl. Gedebage No.186, Arcamanik', 'city' => 'Kota Bandung', 'district' => 'Arcamanik', 'latitude' => -6.910573, 'longitude' => 107.669414, 'jumlah_porsi' => 670, 'data_source' => 'database'],
            ['school_name' => 'MA Negeri 29 Cibiru', 'npsn' => '20304246', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Ahmad Yani No.142, Cibiru', 'city' => 'Kota Bandung', 'district' => 'Cibiru', 'latitude' => -6.905791, 'longitude' => 107.668314, 'jumlah_porsi' => 859, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-145
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-145',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Kutawaringin Konflik 45", "address": "Jl. Konflik Kutawaringin No.4", "district": "Kutawaringin", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 10}', true),
            'form2_data'          => json_decode('{"name": "Admin R45", "email": "admin.r45@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -7.086059,
            'longitude'           => 107.562788,
            'confirmed_latitude'  => -7.08558,
            'confirmed_longitude' => 107.562274,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMAN 4 Katapang', 'npsn' => '20312611', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Buah Batu No.138, Katapang', 'city' => 'Kabupaten Bandung', 'district' => 'Katapang', 'latitude' => -7.047926, 'longitude' => 107.546088, 'jumlah_porsi' => 799, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-146
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-146',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Cangkuang Konflik 46", "address": "Jl. Konflik Cangkuang No.23", "district": "Cangkuang", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 9}', true),
            'form2_data'          => json_decode('{"name": "Admin R46", "email": "admin.r46@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -7.05388,
            'longitude'           => 107.605889,
            'confirmed_latitude'  => -7.053271,
            'confirmed_longitude' => 107.605499,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Baleendah 31', 'npsn' => '20310511', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Gatot Subroto No.4, Baleendah', 'city' => 'Kabupaten Bandung', 'district' => 'Baleendah', 'latitude' => -6.995578, 'longitude' => 107.613119, 'jumlah_porsi' => 440, 'data_source' => 'database'],
            ['school_name' => 'SMAN 4 Katapang', 'npsn' => '20312611', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jalan Buah Batu No.138, Katapang', 'city' => 'Kabupaten Bandung', 'district' => 'Katapang', 'latitude' => -7.047926, 'longitude' => 107.546088, 'jumlah_porsi' => 799, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-147
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-147',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Cisarua Konflik 47", "address": "Jl. Konflik Cisarua No.18", "district": "Cisarua", "city": "Kabupaten Bandung Barat", "province": "Jawa Barat", "capacity": 8}', true),
            'form2_data'          => json_decode('{"name": "Admin R47", "email": "admin.r47@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.798329,
            'longitude'           => 107.625807,
            'confirmed_latitude'  => -6.798363,
            'confirmed_longitude' => 107.626267,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Teknologi Parongpong', 'npsn' => '20309981', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jl. Pemuda No.1, Parongpong', 'city' => 'Kabupaten Bandung Barat', 'district' => 'Parongpong', 'latitude' => -6.796152, 'longitude' => 107.636048, 'jumlah_porsi' => 1148, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-148
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-148',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Baros Konflik 48", "address": "Jl. Konflik Baros No.9", "district": "Baros", "city": "Kota Cimahi", "province": "Jawa Barat", "capacity": 5}', true),
            'form2_data'          => json_decode('{"name": "Admin R48", "email": "admin.r48@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.882158,
            'longitude'           => 107.530518,
            'confirmed_latitude'  => -6.882817,
            'confirmed_longitude' => 107.530086,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMK Teknologi Cimahi Selatan', 'npsn' => '20306940', 'level' => 'SMK', 'school_status' => 'private', 'address' => 'Jl. Pelajar No.155, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.894546, 'longitude' => 107.526389, 'jumlah_porsi' => 1184, 'data_source' => 'database'],
            ['school_name' => 'MI Al-Hikmah 10', 'npsn' => '20306692', 'level' => 'MI', 'school_status' => 'private', 'address' => 'Jl. Kebon Jati No.170, Cimahi Selatan', 'city' => 'Kota Cimahi', 'district' => 'Cimahi Selatan', 'latitude' => -6.894382, 'longitude' => 107.531823, 'jumlah_porsi' => 501, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-149
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-149',
            'submitted_by'        => $userId,
            'source'              => 'internal',
            'form1_data'          => json_decode('{"name": "SPPG Banyuresmi Konflik 49", "address": "Jl. Konflik Banyuresmi No.3", "district": "Banyuresmi", "city": "Kabupaten Garut", "province": "Jawa Barat", "capacity": 5}', true),
            'form2_data'          => json_decode('{"name": "Admin R49", "email": "admin.r49@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -7.1645,
            'longitude'           => 107.783673,
            'confirmed_latitude'  => -7.164723,
            'confirmed_longitude' => 107.783216,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'MI Islamiyah Tarogong Kidul', 'npsn' => '20314755', 'level' => 'MI', 'school_status' => 'private', 'address' => 'Jalan Buah Batu No.57, Tarogong Kidul', 'city' => 'Kabupaten Garut', 'district' => 'Tarogong Kidul', 'latitude' => -7.212452, 'longitude' => 107.86843, 'jumlah_porsi' => 608, 'data_source' => 'database'],
            ['school_name' => 'SMK Teknologi Tarogong Kaler', 'npsn' => '20314250', 'level' => 'SMK', 'school_status' => 'public', 'address' => 'Jalan Asia Afrika No.192, Tarogong Kaler', 'city' => 'Kabupaten Garut', 'district' => 'Tarogong Kaler', 'latitude' => -7.217291, 'longitude' => 107.867918, 'jumlah_porsi' => 408, 'data_source' => 'database'],
        ]);
        $created++;

        // RED | BULK-20260607-150
        $draft = SppgDraft::create([
            'submission_number'   => 'BULK-20260607-150',
            'submitted_by'        => $userId,
            'source'              => 'external',
            'form1_data'          => json_decode('{"name": "SPPG Margahayu Konflik 50", "address": "Jl. Konflik Margahayu No.28", "district": "Margahayu", "city": "Kabupaten Bandung", "province": "Jawa Barat", "capacity": 6}', true),
            'form2_data'          => json_decode('{"name": "Admin R50", "email": "admin.r50@sppg.test", "password": "password123"}', true),
            'form3_data'          => null,
            'latitude'            => -6.992648,
            'longitude'           => 107.554516,
            'confirmed_latitude'  => -6.991881,
            'confirmed_longitude' => 107.554289,
            'point_status'        => 'red',
            'map_confirmed'       => true,
            'status'              => 'draft',
            'submitted_at'        => null,
        ]);
        $draft->partners()->createMany([
            ['school_name' => 'SMA Negeri 44 Banjaran', 'npsn' => '20312701', 'level' => 'SMA', 'school_status' => 'private', 'address' => 'Jl. Setiabudi No.16, Banjaran', 'city' => 'Kabupaten Bandung', 'district' => 'Banjaran', 'latitude' => -7.003379, 'longitude' => 107.539991, 'jumlah_porsi' => 459, 'data_source' => 'database'],
        ]);
        $created++;


        $this->command->info("BulkSppgDraftSeeder selesai: {$created} pengajuan di-seed.");
        $this->command->info("  Hijau  : 50 | Kuning : 50 | Merah  : 50");
    }
}