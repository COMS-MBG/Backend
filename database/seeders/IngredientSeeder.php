<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ingredients = [
            [
                'name'           => 'Beras Putih',
                'carbohydrate'   => 77.10,
                'protein'        => 6.80,
                'calorie'        => 349.00,
                'fat'            => 0.60,
                'serving_weight' => 100.00,
                'description'    => 'Beras putih lokal kualitas medium, sumber karbohidrat utama (berat mentah).',
            ],
            [
                'name'           => 'Dada Ayam',
                'carbohydrate'   => 0.00,
                'protein'        => 31.00,
                'calorie'        => 165.00,
                'fat'            => 3.60,
                'serving_weight' => 100.00,
                'description'    => 'Dada ayam tanpa kulit dan tulang, protein hewani tinggi.',
            ],
            [
                'name'           => 'Paha Ayam',
                'carbohydrate'   => 0.00,
                'protein'        => 26.00,
                'calorie'        => 209.00,
                'fat'            => 10.90,
                'serving_weight' => 100.00,
                'description'    => 'Paha ayam segar dengan kulit, gurih dan berlemak.',
            ],
            [
                'name'           => 'Daging Sapi',
                'carbohydrate'   => 0.00,
                'protein'        => 26.00,
                'calorie'        => 250.00,
                'fat'            => 15.00,
                'serving_weight' => 100.00,
                'description'    => 'Daging sapi segar bagian gandik/paha belakang, tinggi zat besi.',
            ],
            [
                'name'           => 'Telur Ayam',
                'carbohydrate'   => 1.10,
                'protein'        => 12.60,
                'calorie'        => 155.00,
                'fat'            => 10.60,
                'serving_weight' => 100.00,
                'description'    => 'Telur ayam ras segar, sumber protein hewani lengkap dan terjangkau.',
            ],
            [
                'name'           => 'Ikan Kembung',
                'carbohydrate'   => 0.00,
                'protein'        => 21.40,
                'calorie'        => 162.00,
                'fat'            => 8.50,
                'serving_weight' => 100.00,
                'description'    => 'Ikan kembung segar lokal, kaya omega-3 dan protein hewani tinggi.',
            ],
            [
                'name'           => 'Ikan Lele',
                'carbohydrate'   => 0.00,
                'protein'        => 18.00,
                'calorie'        => 105.00,
                'fat'            => 2.90,
                'serving_weight' => 100.00,
                'description'    => 'Ikan lele segar, sumber protein hewani bernilai ekonomis tinggi.',
            ],
            [
                'name'           => 'Tempe Murni',
                'carbohydrate'   => 9.00,
                'protein'        => 20.80,
                'calorie'        => 193.00,
                'fat'            => 10.80,
                'serving_weight' => 100.00,
                'description'    => 'Tempe kedelai murni tradisional, kaya protein nabati dan serat.',
            ],
            [
                'name'           => 'Tahu Putih',
                'carbohydrate'   => 1.90,
                'protein'        => 8.00,
                'calorie'        => 76.00,
                'fat'            => 4.80,
                'serving_weight' => 100.00,
                'description'    => 'Tahu putih sutra segar, protein nabati lembut yang mudah dicerna.',
            ],
            [
                'name'           => 'Wortel Segar',
                'carbohydrate'   => 9.60,
                'protein'        => 0.90,
                'calorie'        => 41.00,
                'fat'            => 0.20,
                'serving_weight' => 100.00,
                'description'    => 'Wortel lokal segar, kaya beta-karoten dan vitamin A.',
            ],
            [
                'name'           => 'Bayam Hijau',
                'carbohydrate'   => 3.60,
                'protein'        => 2.90,
                'calorie'        => 23.00,
                'fat'            => 0.40,
                'serving_weight' => 100.00,
                'description'    => 'Daun bayam hijau segar, kaya zat besi dan serat alami.',
            ],
            [
                'name'           => 'Brokoli',
                'carbohydrate'   => 6.60,
                'protein'        => 2.80,
                'calorie'        => 34.00,
                'fat'            => 0.40,
                'serving_weight' => 100.00,
                'description'    => 'Brokoli hijau segar, tinggi vitamin C dan antioksidan.',
            ],
            [
                'name'           => 'Buncis',
                'carbohydrate'   => 7.00,
                'protein'        => 1.80,
                'calorie'        => 31.00,
                'fat'            => 0.20,
                'serving_weight' => 100.00,
                'description'    => 'Buncis muda segar, kaya akan vitamin A, C, K, serta mineral.',
            ],
            [
                'name'           => 'Labu Siam',
                'carbohydrate'   => 4.50,
                'protein'        => 0.80,
                'calorie'        => 19.00,
                'fat'            => 0.10,
                'serving_weight' => 100.00,
                'description'    => 'Labu siam segar, berair, rendah kalori, sangat baik untuk hidangan sayur bening.',
            ],
            [
                'name'           => 'Kentang',
                'carbohydrate'   => 17.00,
                'protein'        => 2.00,
                'calorie'        => 77.00,
                'fat'            => 0.10,
                'serving_weight' => 100.00,
                'description'    => 'Kentang granola lokal, karbohidrat alternatif pengganti nasi.',
            ],
            [
                'name'           => 'Susu UHT Plain',
                'carbohydrate'   => 4.80,
                'protein'        => 3.20,
                'calorie'        => 60.00,
                'fat'            => 3.20,
                'serving_weight' => 100.00,
                'description'    => 'Susu cair UHT plain tawar, kalsium untuk pertumbuhan anak.',
            ],
            [
                'name'           => 'Minyak Sawit',
                'carbohydrate'   => 0.00,
                'protein'        => 0.00,
                'calorie'        => 884.00,
                'fat'            => 100.00,
                'serving_weight' => 100.00,
                'description'    => 'Minyak goreng kelapa sawit, sebagai media memasak dan sumber lemak.',
            ],
            [
                'name'           => 'Mentega',
                'carbohydrate'   => 0.10,
                'protein'        => 0.90,
                'calorie'        => 717.00,
                'fat'            => 81.00,
                'serving_weight' => 100.00,
                'description'    => 'Mentega asin (salted butter) untuk tumisan aromatik.',
            ],
            [
                'name'           => 'Gula Pasir',
                'carbohydrate'   => 100.00,
                'protein'        => 0.00,
                'calorie'        => 387.00,
                'fat'            => 0.00,
                'serving_weight' => 100.00,
                'description'    => 'Gula pasir kristal putih untuk penyeimbang rasa masakan.',
            ],
            [
                'name'           => 'Garam Dapur',
                'carbohydrate'   => 0.00,
                'protein'        => 0.00,
                'calorie'        => 0.00,
                'fat'            => 0.00,
                'serving_weight' => 100.00,
                'description'    => 'Garam beriodium sebagai bumbu dasar rasa asin.',
            ],
            [
                'name'           => 'Bawang Merah',
                'carbohydrate'   => 9.30,
                'protein'        => 1.20,
                'calorie'        => 39.00,
                'fat'            => 0.10,
                'serving_weight' => 100.00,
                'description'    => 'Bawang merah lokal kupas untuk bumbu dasar masakan.',
            ],
            [
                'name'           => 'Bawang Putih',
                'carbohydrate'   => 33.10,
                'protein'        => 6.40,
                'calorie'        => 149.00,
                'fat'            => 0.50,
                'serving_weight' => 100.00,
                'description'    => 'Bawang putih kating kupas untuk aroma aromatik bumbu.',
            ],
        ];

        foreach ($ingredients as $data) {
            Ingredient::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        $this->command->info('Ingredient seeder selesai: ' . count($ingredients) . ' bahan baku berhasil ditambahkan.');
    }
}
