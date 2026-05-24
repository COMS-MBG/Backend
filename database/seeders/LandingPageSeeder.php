<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LandingContent;
use App\Models\Feedback;

class LandingPageSeeder extends Seeder
{
    public function run(): void
    {
        // ── Landing Content ──────────────────────────────────────────
        LandingContent::updateOrCreate(
            ['section_name' => 'hero'],
            [
                'title'       => 'Satuan Pelayanan Sumur Bandung',
                'description' => 'Menyediakan asupan nutrisi seimbang untuk generasi unggul. Setiap hidangan diproses dengan standar keamanan pangan tertinggi dan pengawasan ahli gizi profesional.',
                'image_path'  => null,
                'is_active'   => true,
            ]
        );

        LandingContent::updateOrCreate(
            ['section_name' => 'transparency'],
            [
                'title'       => 'Transparansi & Aspirasi',
                'description' => 'Kami percaya bahwa masukan dari masyarakat, terutama wali murid, adalah kunci untuk terus meningkatkan kualitas pelayanan gizi nasional.',
                'image_path'  => null,
                'is_active'   => true,
            ]
        );

        // ── Feedback (Testimoni) ──────────────────────────────────────
        $testimonials = [
            [
                'name'        => 'Ibu Sarah K.',
                'role'        => 'Wali Murid SDN 12 Bandung',
                'message'     => 'Sangat puas dengan kualitas makannya. Anak saya sekarang lebih semangat ke sekolah dan nafsu makannya meningkat. Porsinya juga pas untuk anak SD.',
                'rating'      => 5,
                'is_approved' => true,
            ],
            [
                'name'        => 'Bapak Ahmad R.',
                'role'        => 'Wali Murid SDN 7 Bandung',
                'message'     => 'Menu yang diberikan sangat variasi, setiap hari berbeda jadi anak tidak bosan. Terima kasih Badan Gizi Nasional sudah hadir di wilayah kami.',
                'rating'      => 4,
                'is_approved' => true,
            ],
            [
                'name'        => 'Ibu Maya S.',
                'role'        => 'Wali Murid SMPN 3 Bandung',
                'message'     => 'Anak saya bilang ayam bumbu kuningnya sangat enak. Kebersihan kemasan juga sangat terjaga. Harapannya program ini terus berkelanjutan.',
                'rating'      => 4,
                'is_approved' => true,
            ],
        ];

        foreach ($testimonials as $data) {
            Feedback::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        $this->command->info('✅ LandingPageSeeder selesai: LandingContent & Feedback berhasil diisi.');
    }
}
