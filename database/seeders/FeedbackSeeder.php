<?php

namespace Database\Seeders;

use App\Models\Feedback;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $feedbacks = [
            [
                'name'        => 'Sri Wahyuni',
                'role'        => 'Wali Murid',
                'message'     => 'Anak saya sangat menyukai menu Nasi Ayam Bakar Madunya. Porsinya cukup kenyang dan makanannya dikirim tepat waktu sebelum jam istirahat sekolah.',
                'rating'      => 5,
                'is_approved' => true,
            ],
            [
                'name'        => 'Budi Hermawan',
                'role'        => 'Guru Kelas',
                'message'     => 'Sup bening bayamnya segar dan bersih. Anak-anak di kelas saya jadi lahap makannya. Program ini sangat membantu konsentrasi belajar siswa di siang hari.',
                'rating'      => 5,
                'is_approved' => true,
            ],
            [
                'name'        => 'Hendra Wijaya',
                'role'        => 'Wali Murid',
                'message'     => 'Sangat mengapresiasi informasi kandungan gizi yang transparan di website. Sebagai orang tua, saya jadi merasa tenang dengan asupan nutrisi anak saya.',
                'rating'      => 4,
                'is_approved' => true,
            ],
            [
                'name'        => 'Rian Pratama',
                'role'        => 'Siswa SMA',
                'message'     => 'Makanannya enak-enak, terutama menu daging sapi lada hitam. Tapi tolong untuk buah pencuci mulutnya kalau bisa divariasikan selain pisang.',
                'rating'      => 4,
                'is_approved' => true,
            ],
            [
                'name'        => 'Tatang',
                'role'        => 'Masyarakat Umum',
                'message'     => 'Web ini sangat bagus, pendaftaran SPPG baru juga cepat direspon. Terus tingkatkan kualitas pelayanannya.',
                'rating'      => 5,
                'is_approved' => false, // Menunggu persetujuan moderator
            ],
        ];

        foreach ($feedbacks as $data) {
            Feedback::create($data);
        }

        $this->command->info('Feedback seed selesai: ' . count($feedbacks) . ' ulasan.');
    }
}
