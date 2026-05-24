<?php

namespace App\Http\Controllers;

use App\Models\LandingContent;
use App\Models\Menu;
use App\Models\Feedback;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function index()
    {
        // 1. Ambil data teks untuk Landing Page
        // (Asumsi kamu belum buat scopeActive, kita pakai where biasa dulu)
        $hero = LandingContent::where('section_name', 'hero')->first();
        $transparency = LandingContent::where('section_name', 'transparency')->first();

        // 2. Ambil data Menu (Jadwal Makanan) - misalnya dibatasi 4 data terbaru
        $menus = Menu::limit(4)->get();

        // 3. Ambil data Feedback (Ulasan Masyarakat) yang sudah disetujui - dibatasi 3 data
        $feedbacks = Feedback::approved()->latest()->limit(3)->get();

        // 4. Lempar semua data ke file View (Blade)
        return view('public.landing', compact('hero', 'transparency', 'menus', 'feedbacks'));
    }
}