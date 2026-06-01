<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $weeklyMenus = [
            [
                'day' => 'Senin',
                'day_name' => 'Senin',
                'menu' => 'Nasi Ayam Bumbu Kuning',
                'calories' => '450',
                'protein' => '32',
                'carbs' => '55',
                'fat' => '12',
                'image' => 'https://images.unsplash.com/photo-1603048588665-791ca8aea617?w=600&q=80',
                'is_today' => true,
            ],
            [
                'day' => 'Selasa',
                'day_name' => 'Selasa',
                'menu' => 'Ikan Bakar Bumbu Rujak',
                'calories' => '430',
                'protein' => '28',
                'carbs' => '50',
                'fat' => '10',
                'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80',
                'is_today' => false,
            ],
            [
                'day' => 'Rabu',
                'day_name' => 'Rabu',
                'menu' => 'Telur Balado & Tempe Orek',
                'calories' => '460',
                'protein' => '30',
                'carbs' => '52',
                'fat' => '15',
                'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80',
                'is_today' => false,
            ],
            [
                'day' => 'Kamis',
                'day_name' => 'Kamis',
                'menu' => 'Ayam Suwir & Perkedel',
                'calories' => '480',
                'protein' => '35',
                'carbs' => '45',
                'fat' => '14',
                'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80',
                'is_today' => false,
            ],
            [
                'day' => 'Jumat',
                'day_name' => 'Jumat',
                'menu' => 'Daging Sapi Lada Hitam',
                'calories' => '470',
                'protein' => '34',
                'carbs' => '48',
                'fat' => '16',
                'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80',
                'is_today' => false,
            ],
        ];

        // Dummy Data untuk Ulasan
        $reviews = [
            [
                'name' => 'Budi Santoso',
                'rating' => 5,
                'comment' => 'Program ini sangat membantu anak-anak di sekolah kami untuk mendapatkan gizi seimbang.'
            ],
            [
                'name' => 'Siti Aminah',
                'rating' => 4,
                'comment' => 'Menu makanannya bervariasi dan anak saya suka sekali.'
            ],
            [
                'name' => 'Ahmad Fauzi',
                'rating' => 5,
                'comment' => 'Kualitas makanan dan higienitas sangat terjaga.'
            ],
        ];

        // Dummy Data untuk Mitra (Hanya untuk keperluan iterasi di view)
        $partners = [
            'Dinas Kesehatan', 'Kementerian Pendidikan', 'BKKBN', 'Puskesmas Setempat', 'PKK', 'Petani Lokal'
        ];

        return view('landing.index', compact('weeklyMenus', 'reviews', 'partners'));
    }
}
