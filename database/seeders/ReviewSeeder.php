<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\SPPG;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil beberapa SPPG ID yang ada
        $sppgIds = SPPG::pluck('id')->toArray();
        $fallbackSppgId = $sppgIds[0] ?? null;

        $reviews = [
            [
                'sppg_id' => $sppgIds[0] ?? $fallbackSppgId,
                'name'    => 'Budi Santoso',
                'email'   => 'budi.santoso@email.com',
                'rating'  => 5,
                'comment' => 'Program ini sangat membantu anak-anak di sekolah kami untuk mendapatkan gizi seimbang. Terima kasih SPPG!',
                'is_approved' => true,
            ],
            [
                'sppg_id' => $sppgIds[1] ?? $fallbackSppgId,
                'name'    => 'Siti Aminah',
                'email'   => 'siti.aminah@email.com',
                'rating'  => 4,
                'comment' => 'Menu makanannya bervariasi dan anak saya suka sekali. Semoga bisa terus ditingkatkan kualitasnya.',
                'is_approved' => true,
            ],
            [
                'sppg_id' => $sppgIds[2] ?? $fallbackSppgId,
                'name'    => 'Ahmad Fauzi',
                'email'   => 'ahmad.fauzi@email.com',
                'rating'  => 5,
                'comment' => 'Kualitas makanan dan higienitas sangat terjaga. Sangat merekomendasikan program ini.',
                'is_approved' => true,
            ],
            [
                'sppg_id' => $sppgIds[0] ?? $fallbackSppgId,
                'name'    => 'Dewi Lestari',
                'email'   => 'dewi.lestari@email.com',
                'rating'  => 5,
                'comment' => 'Anak-anak jadi lebih semangat ke sekolah karena ada program makan bergizi gratis ini.',
                'is_approved' => true,
            ],
            [
                'sppg_id' => $sppgIds[1] ?? $fallbackSppgId,
                'name'    => 'Rudi Hartono',
                'email'   => 'rudi.hartono@email.com',
                'rating'  => 4,
                'comment' => 'Pelayanan baik, tapi kadang jadwal pengiriman sedikit terlambat. Secara keseluruhan memuaskan.',
                'is_approved' => true,
            ],
            [
                'sppg_id' => $sppgIds[2] ?? $fallbackSppgId,
                'name'    => 'Mahardhitya Pratama',
                'email'   => 'mahardhitya@email.com',
                'rating'  => 5,
                'comment' => 'Luar biasa! Porsi cukup, rasa enak, dan yang paling penting gizinya seimbang.',
                'is_approved' => true,
            ],
        ];

        foreach ($reviews as $review) {
            Review::create($review);
        }
    }
}
