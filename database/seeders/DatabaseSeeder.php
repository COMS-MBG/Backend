<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Urutan penting berdasarkan dependensi:
     * 1. Permissions & Roles
     * 2. SPPG, Users, & Employees (termasuk Kurir)
     * 3. Schools & SPPGSchool Pivot
     * 4. Partners & Bahan Baku / Resep / Menu
     * 5. Jadwal Pengiriman & Riwayat
     * 6. Feedback, PublicUsers, & Data Dummy Lainnya
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            SPPGSeeder::class,
            UserSeeder::class,
            SchoolSeeder::class,       // Sebelummya terlewat
            SPPGSchoolSeeder::class,   // Pivot SPPG-School
            PartnerSeeder::class,
            IngredientSeeder::class,
            RecipeSeeder::class,
            DistributionSeeder::class,
            MenuSeeder::class,
            DeliveryScheduleSeeder::class,
            FeedbackSeeder::class,
            PublicUserSeeder::class,
            FinancialReportSeeder::class,
            RatingSeeder::class,
            RecommendationSeeder::class,
            SPPGSubmissionSeeder::class,
        ]);
    }
}
