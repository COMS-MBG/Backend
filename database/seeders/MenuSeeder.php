<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Recipe;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recipes = Recipe::all();

        if ($recipes->isEmpty()) {
            $this->command->error('Gagal menjalankan MenuSeeder: Resep kosong. Jalankan RecipeSeeder terlebih dahulu.');
            return;
        }

        $now = Carbon::now();

        // ────────── 1. MENU MINGGU KEMARIN (STATUS: ARCHIVED) ──────────
        // Menggunakan rentang tanggal yang sudah lewat
        $lastWeekStart = $now->copy()->subWeek()->startOfWeek();
        $lastWeekEnd = $now->copy()->subWeek()->endOfWeek();

        $menuLastWeek = Menu::firstOrCreate(
            ['name' => 'Menu Nutrisi Sehat - Minggu Lalu'],
            [
                'week_start' => $lastWeekStart->toDateString(),
                'week_end'   => $lastWeekEnd->toDateString(),
                'status'     => 'archived',
                'notes'      => 'Evaluasi: Menu berjalan sukses, tingkat konsumsi siswa mencapai 95%.',
            ]
        );

        $this->seedMenuItems($menuLastWeek, $recipes);

        // ────────── 2. MENU MINGGU BERJALAN (STATUS: PUBLISHED) ──────────
        $thisWeekStart = $now->copy()->startOfWeek();
        $thisWeekEnd = $now->copy()->endOfWeek();

        $menuThisWeek = Menu::firstOrCreate(
            ['name' => 'Menu Nutrisi Sehat - Minggu Berjalan'],
            [
                'week_start' => $thisWeekStart->toDateString(),
                'week_end'   => $thisWeekEnd->toDateString(),
                'status'     => 'published',
                'notes'      => 'Menu utama untuk minggu ini. Fokus pada tinggi serat dan kalsium.',
            ]
        );

        $this->seedMenuItems($menuThisWeek, $recipes);

        // ────────── 3. MENU MINGGU DEPAN (STATUS: SCHEDULED) ──────────
        $nextWeekStart = $now->copy()->addWeek()->startOfWeek();
        $nextWeekEnd = $now->copy()->addWeek()->endOfWeek();

        $menuNextWeek = Menu::firstOrCreate(
            ['name' => 'Menu Nutrisi Sehat - Rencana Minggu Depan'],
            [
                'week_start' => $nextWeekStart->toDateString(),
                'week_end'   => $nextWeekEnd->toDateString(),
                'status'     => 'scheduled',
                'notes'      => 'Menu dijadwalkan untuk minggu depan. Menunggu finalisasi bahan baku dari logistik.',
            ]
        );

        $this->seedMenuItems($menuNextWeek, $recipes);

        // ────────── 4. MENU 2 MINGGU DEPAN (STATUS: PLANNED) ──────────
        $futureWeekStart = $now->copy()->addWeeks(2)->startOfWeek();
        $futureWeekEnd = $now->copy()->addWeeks(2)->endOfWeek();

        $menuFutureWeek = Menu::firstOrCreate(
            ['name' => 'Menu Nutrisi Sehat - Draft Masa Depan'],
            [
                'week_start' => $futureWeekStart->toDateString(),
                'week_end'   => $futureWeekEnd->toDateString(),
                'status'     => 'planned',
                'notes'      => 'Rencana menu 2 minggu ke depan. Masih dalam proses penyusunan gizi.',
            ]
        );

        $this->seedMenuItems($menuFutureWeek, $recipes);

        $this->command->info('Menu & MenuItems seed selesai.');
    }

    /**
     * Seed menu items for a specific menu using available recipes.
     */
    private function seedMenuItems(Menu $menu, $recipes): void
    {
        // Hapus item lama jika ada (untuk re-run seeder yang bersih)
        MenuItem::where('menu_id', $menu->id)->delete();

        // 1=Senin s.d 5=Jumat (hari sekolah aktif)
        for ($day = 1; $day <= 5; $day++) {
            // Pilih resep secara acak dari database resep
            $recipe = $recipes->values()->get(($day - 1) % $recipes->count());

            MenuItem::create([
                'menu_id'     => $menu->id,
                'recipe_id'   => $recipe->id,
                'day_of_week' => $day,
                'order'       => 1,
            ]);
        }
    }
}
