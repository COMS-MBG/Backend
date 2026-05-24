<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\Ingredient;
use App\Services\SPPG\RecipeService;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hubungkan ke RecipeService untuk kalkulasi gizi otomatis saat seeding
        $recipeService = app(RecipeService::class);

        // Ambil data bahan baku dari database agar aman dan akurat
        $beras = Ingredient::where('name', 'Beras Putih')->first();
        $dadaAyam = Ingredient::where('name', 'Dada Ayam')->first();
        $pahaAyam = Ingredient::where('name', 'Paha Ayam')->first();
        $dagingSapi = Ingredient::where('name', 'Daging Sapi')->first();
        $telur = Ingredient::where('name', 'Telur Ayam')->first();
        $ikanKembung = Ingredient::where('name', 'Ikan Kembung')->first();
        $ikanLele = Ingredient::where('name', 'Ikan Lele')->first();
        $tempe = Ingredient::where('name', 'Tempe Murni')->first();
        $tahu = Ingredient::where('name', 'Tahu Putih')->first();
        $wortel = Ingredient::where('name', 'Wortel Segar')->first();
        $bayam = Ingredient::where('name', 'Bayam Hijau')->first();
        $brokoli = Ingredient::where('name', 'Brokoli')->first();
        $buncis = Ingredient::where('name', 'Buncis')->first();
        $labuSiam = Ingredient::where('name', 'Labu Siam')->first();
        $minyakSawit = Ingredient::where('name', 'Minyak Sawit')->first();
        $mentega = Ingredient::where('name', 'Mentega')->first();
        $gula = Ingredient::where('name', 'Gula Pasir')->first();
        $garam = Ingredient::where('name', 'Garam Dapur')->first();
        $bawangMerah = Ingredient::where('name', 'Bawang Merah')->first();
        $bawangPutih = Ingredient::where('name', 'Bawang Putih')->first();

        // Pastikan bahan baku utama ada sebelum seeding resep
        if (!$beras || !$dadaAyam || !$minyakSawit) {
            $this->command->error('Gagal menjalankan RecipeSeeder: Bahan baku utama (Beras Putih, Dada Ayam, Minyak Sawit) tidak ditemukan di database. Pastikan IngredientSeeder telah dijalankan.');
            return;
        }

        $recipes = [
            [
                'name' => 'Nasi Ayam Bakar Madu & Sayur Tumis',
                'description' => 'Paket nasi dengan ayam bakar bumbu madu manis gurih, tempe bacem goreng, dan tumis buncis wortel. Cocok untuk makan siang anak sekolah.',
                'target_calorie' => 2400.00,
                'target_protein' => 150.00,
                'target_carbohydrate' => 350.00,
                'target_fat' => 40.00,
                'ingredients' => [
                    ['ingredient_id' => $beras->id, 'weight_used' => 400.00],
                    ['ingredient_id' => $dadaAyam->id, 'weight_used' => 300.00],
                    ['ingredient_id' => $tempe->id, 'weight_used' => 150.00],
                    ['ingredient_id' => $minyakSawit->id, 'weight_used' => 10.00],
                    ['ingredient_id' => $wortel->id, 'weight_used' => 100.00],
                    ['ingredient_id' => $buncis->id, 'weight_used' => 100.00],
                    ['ingredient_id' => $bawangMerah->id, 'weight_used' => 20.00],
                    ['ingredient_id' => $bawangPutih->id, 'weight_used' => 10.00],
                    ['ingredient_id' => $gula->id, 'weight_used' => 10.00],
                    ['ingredient_id' => $garam->id, 'weight_used' => 5.00],
                ]
            ],
            [
                'name' => 'Nasi Daging Sapi Lada Hitam & Tumis Brokoli',
                'description' => 'Menu nasi dengan tumisan daging sapi saus lada hitam gurih, tahu goreng sutra, dan tumis brokoli wortel segar yang kaya akan vitamin C.',
                'target_calorie' => 2450.00,
                'target_protein' => 100.00,
                'target_carbohydrate' => 320.00,
                'target_fat' => 60.00,
                'ingredients' => [
                    ['ingredient_id' => $beras->id, 'weight_used' => 350.00],
                    ['ingredient_id' => $dagingSapi->id, 'weight_used' => 250.00],
                    ['ingredient_id' => $tahu->id, 'weight_used' => 200.00],
                    ['ingredient_id' => $minyakSawit->id, 'weight_used' => 15.00],
                    ['ingredient_id' => $brokoli->id, 'weight_used' => 200.00],
                    ['ingredient_id' => $wortel->id, 'weight_used' => 100.00],
                    ['ingredient_id' => $bawangMerah->id, 'weight_used' => 20.00],
                    ['ingredient_id' => $bawangPutih->id, 'weight_used' => 15.00],
                    ['ingredient_id' => $gula->id, 'weight_used' => 10.00],
                    ['ingredient_id' => $garam->id, 'weight_used' => 5.00],
                ]
            ],
            [
                'name' => 'Nasi Ikan Kembung Goreng & Sup Bening Bayam',
                'description' => 'Paket nasi dengan ikan kembung goreng renyah kaya omega-3, tahu goreng lembut, dan sup bayam labu siam bening yang hangat dan segar.',
                'target_calorie' => 2300.00,
                'target_protein' => 90.00,
                'target_carbohydrate' => 360.00,
                'target_fat' => 45.00,
                'ingredients' => [
                    ['ingredient_id' => $beras->id, 'weight_used' => 400.00],
                    ['ingredient_id' => $ikanKembung->id, 'weight_used' => 250.00],
                    ['ingredient_id' => $tempe->id, 'weight_used' => 100.00],
                    ['ingredient_id' => $minyakSawit->id, 'weight_used' => 15.00],
                    ['ingredient_id' => $bayam->id, 'weight_used' => 150.00],
                    ['ingredient_id' => $labuSiam->id, 'weight_used' => 100.00],
                    ['ingredient_id' => $bawangMerah->id, 'weight_used' => 15.00],
                    ['ingredient_id' => $bawangPutih->id, 'weight_used' => 10.00],
                    ['ingredient_id' => $gula->id, 'weight_used' => 5.00],
                    ['ingredient_id' => $garam->id, 'weight_used' => 5.00],
                ]
            ],
            [
                'name' => 'Nasi Opor Ayam Kuning & Tumis Labu Siam',
                'description' => 'Paket nasi dengan opor ayam kuning paha ayam empuk, telur rebus, dan tumis labu siam gurih untuk asupan protein dan vitamin.',
                'target_calorie' => 2350.00,
                'target_protein' => 110.00,
                'target_carbohydrate' => 310.00,
                'target_fat' => 65.00,
                'ingredients' => [
                    ['ingredient_id' => $beras->id, 'weight_used' => 350.00],
                    ['ingredient_id' => $pahaAyam->id, 'weight_used' => 250.00],
                    ['ingredient_id' => $telur->id, 'weight_used' => 100.00],
                    ['ingredient_id' => $minyakSawit->id, 'weight_used' => 10.00],
                    ['ingredient_id' => $labuSiam->id, 'weight_used' => 150.00],
                    ['ingredient_id' => $wortel->id, 'weight_used' => 100.00],
                    ['ingredient_id' => $bawangMerah->id, 'weight_used' => 20.00],
                    ['ingredient_id' => $bawangPutih->id, 'weight_used' => 15.00],
                    ['ingredient_id' => $gula->id, 'weight_used' => 10.00],
                    ['ingredient_id' => $garam->id, 'weight_used' => 5.00],
                ]
            ],
            [
                'name' => 'Nasi Goreng Ayam Spesial',
                'description' => 'Nasi goreng dengan potongan ayam fillet, telur orak-arik, sayuran segar, dan bumbu spesial yang lezat.',
                'target_calorie' => 2500.00,
                'target_protein' => 85.00,
                'target_carbohydrate' => 320.00,
                'target_fat' => 60.00,
                'ingredients' => [
                    ['ingredient_id' => $beras->id, 'weight_used' => 300.00],
                    ['ingredient_id' => $dadaAyam->id, 'weight_used' => 200.00],
                    ['ingredient_id' => $telur->id, 'weight_used' => 100.00],
                    ['ingredient_id' => $minyakSawit->id, 'weight_used' => 15.00],
                    ['ingredient_id' => $wortel->id, 'weight_used' => 100.00],
                    ['ingredient_id' => $bawangMerah->id, 'weight_used' => 20.00],
                    ['ingredient_id' => $bawangPutih->id, 'weight_used' => 15.00],
                    ['ingredient_id' => $gula->id, 'weight_used' => 10.00],
                    ['ingredient_id' => $garam->id, 'weight_used' => 5.00],
                ]
            ],
            [
                'name' => ' Soto Ayam Bening',
                'description' => 'Soto ayam bening dengan kuah kaldu ayam gurih, suwiran daging ayam, telur rebus, dan taburan daun bawang serta seledri.',
                'target_calorie' => 2200.00,
                'target_protein' => 70.00,
                'target_carbohydrate' => 330.00,
                'target_fat' => 55.00,
                'ingredients' => [
                    ['ingredient_id' => $beras->id, 'weight_used' => 350.00],
                    ['ingredient_id' => $dadaAyam->id, 'weight_used' => 250.00],
                    ['ingredient_id' => $telur->id, 'weight_used' => 100.00],
                    ['ingredient_id' => $minyakSawit->id, 'weight_used' => 10.00],
                    ['ingredient_id' => $bawangMerah->id, 'weight_used' => 20.00],
                    ['ingredient_id' => $bawangPutih->id, 'weight_used' => 15.00],
                    ['ingredient_id' => $gula->id, 'weight_used' => 5.00],
                    ['ingredient_id' => $garam->id, 'weight_used' => 5.00],
                ]
            ],
            [
                'name' => 'Rawon Daging Sapi',
                'description' => 'Rawon daging sapi dengan kuah hitam khas kluwek yang kaya rempah, disajikan dengan tauge, telur asin, dan sambal.',
                'target_calorie' => 2600.00,
                'target_protein' => 95.00,
                'target_carbohydrate' => 310.00,
                'target_fat' => 70.00,
                'ingredients' => [
                    ['ingredient_id' => $beras->id, 'weight_used' => 350.00],
                    ['ingredient_id' => $dagingSapi->id, 'weight_used' => 300.00],
                    ['ingredient_id' => $telur->id, 'weight_used' => 100.00],
                    ['ingredient_id' => $minyakSawit->id, 'weight_used' => 15.00],
                    ['ingredient_id' => $bawangMerah->id, 'weight_used' => 25.00],
                    ['ingredient_id' => $bawangPutih->id, 'weight_used' => 20.00],
                    ['ingredient_id' => $gula->id, 'weight_used' => 10.00],
                    ['ingredient_id' => $garam->id, 'weight_used' => 5.00],
                ]
            ],
            [
                'name' => 'Gado-Gado Sayuran',
                'description' => 'Salad sayuran segar dengan tahu dan tempe goreng, disiram saus kacang spesial yang creamy dan lezat.',
                'target_calorie' => 2350.00,
                'target_protein' => 75.00,
                'target_carbohydrate' => 340.00,
                'target_fat' => 58.00,
                'ingredients' => [
                    ['ingredient_id' => $beras->id, 'weight_used' => 350.00],
                    ['ingredient_id' => $tahu->id, 'weight_used' => 150.00],
                    ['ingredient_id' => $tempe->id, 'weight_used' => 150.00],
                    ['ingredient_id' => $minyakSawit->id, 'weight_used' => 10.00],
                    ['ingredient_id' => $wortel->id, 'weight_used' => 100.00],
                    ['ingredient_id' => $bayam->id, 'weight_used' => 150.00],
                    ['ingredient_id' => $bawangMerah->id, 'weight_used' => 15.00],
                    ['ingredient_id' => $bawangPutih->id, 'weight_used' => 10.00],
                    ['ingredient_id' => $gula->id, 'weight_used' => 5.00],
                    ['ingredient_id' => $garam->id, 'weight_used' => 5.00],
                ]
            ],
        ];

        foreach ($recipes as $data) {
            // Gunakan firstOrCreate berdasarkan nama resep
            $existing = Recipe::where('name', $data['name'])->first();
            if ($existing) {
                continue;
            }

            // Panggil RecipeService untuk menghitung gizi secara otomatis dan menyimpan
            $recipeService->create($data);
        }

        $this->command->info('Recipe seeder selesai: ' . count($recipes) . ' data resep berhasil ditambahkan.');
    }
}
