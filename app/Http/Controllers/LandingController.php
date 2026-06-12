<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Menu;
use App\Models\Review;
use App\Models\OtpVerification;
use App\Models\SPPG;
use App\Models\SppgDraft;
use App\Mail\OtpReviewMail;
use Carbon\Carbon;

class LandingController extends Controller
{
    public function index()
    {
        // ── Ambil menu minggu ini (published) atau yang paling dekat ──
        $currentMenu = Menu::with(['menuItems' => function ($q) {
                $q->with('recipe')->orderBy('day_of_week')->orderBy('order');
            }])
            ->where('status', 'published')
            ->orderBy('week_start', 'desc')
            ->first();

        $weeklyMenus = [];
        $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis'];
        $todayDayOfWeek = Carbon::now()->dayOfWeekIso; // 1=Senin, 7=Minggu

        if ($currentMenu) {
            foreach ($currentMenu->menuItems as $item) {
                if (!$item->recipe) continue;

                $weeklyMenus[] = [
                    'day'      => $dayNames[$item->day_of_week] ?? 'N/A',
                    'day_name' => $dayNames[$item->day_of_week] ?? 'N/A',
                    'menu'     => $item->recipe->name,
                    'calories' => round($item->recipe->total_calorie ?? 0),
                    'protein'  => round($item->recipe->total_protein ?? 0),
                    'carbs'    => round($item->recipe->total_carbohydrate ?? 0),
                    'fat'      => round($item->recipe->total_fat ?? 0),
                    'image'    => 'https://images.unsplash.com/photo-1603048588665-791ca8aea617?w=600&q=80',
                    'is_today' => $item->day_of_week === $todayDayOfWeek,
                ];
            }
        }

        // Fallback: jika belum ada menu di DB, pakai dummy agar view tidak error
        if (empty($weeklyMenus)) {
            $weeklyMenus = [
                ['day' => 'Senin',  'day_name' => 'Senin',  'menu' => 'Nasi Ayam Bumbu Kuning',   'calories' => 450, 'protein' => 32, 'carbs' => 55, 'fat' => 12, 'image' => 'https://images.unsplash.com/photo-1603048588665-791ca8aea617?w=600&q=80', 'is_today' => true],
                ['day' => 'Selasa', 'day_name' => 'Selasa', 'menu' => 'Ikan Bakar Bumbu Rujak',   'calories' => 430, 'protein' => 28, 'carbs' => 50, 'fat' => 10, 'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80', 'is_today' => false],
                ['day' => 'Rabu',   'day_name' => 'Rabu',   'menu' => 'Telur Balado & Tempe Orek', 'calories' => 460, 'protein' => 30, 'carbs' => 52, 'fat' => 15, 'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80', 'is_today' => false],
                ['day' => 'Kamis',  'day_name' => 'Kamis',  'menu' => 'Ayam Suwir & Perkedel',    'calories' => 480, 'protein' => 35, 'carbs' => 45, 'fat' => 14, 'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80', 'is_today' => false],
            ];
        }

        // ── Ambil ulasan yang sudah disetujui dari database ──
        $reviews = Review::approved()
            ->with('sppg:id,name')
            ->latest()
            ->limit(6)
            ->get();

        // Dummy Data untuk Mitra (Hanya untuk keperluan iterasi di view)
        $partners = [
            'Dinas Kesehatan', 'Kementerian Pendidikan', 'BKKBN', 'Puskesmas Setempat', 'PKK', 'Petani Lokal'
        ];

        // ── Ambil daftar SPPG aktif untuk dropdown di modal ulasan ──
        $sppgList = SPPG::where('status', 'active')
            ->select('id', 'name', 'city')
            ->orderBy('name')
            ->get();

        return view('landing.index', compact('weeklyMenus', 'reviews', 'partners', 'sppgList'));
    }

    /**
     * Kirim OTP 6 digit ke email pengguna.
     * POST /review/send-otp
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:100',
            'sppg_id' => 'required|exists:s_p_p_g_s,id',
        ]);

        // Invalidasi semua OTP lama untuk email ini
        OtpVerification::where('email', $request->email)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // Generate 6 digit OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Simpan ke database
        OtpVerification::create([
            'email'      => $request->email,
            'otp_code'   => $otpCode,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // Kirim email
        Mail::to($request->email)->send(new OtpReviewMail($otpCode, $request->name));

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP telah dikirim ke email Anda.',
        ]);
    }

    /**
     * Verifikasi OTP 6 digit.
     * POST /review/verify-otp
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        $otp = OtpVerification::where('email', $request->email)
            ->where('otp_code', $request->otp)
            ->where('is_used', false)
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid atau sudah kedaluwarsa.',
            ], 422);
        }

        // Tandai OTP sudah digunakan
        $otp->update(['is_used' => true]);

        return response()->json([
            'success' => true,
            'message' => 'OTP berhasil diverifikasi.',
        ]);
    }

    /**
     * Simpan ulasan ke database.
     * POST /review/store
     */
    public function storeReview(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:100',
            'sppg_id' => 'required|exists:s_p_p_g_s,id',
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        // Cek apakah email sudah pernah verifikasi OTP (cek ada record used)
        $verified = OtpVerification::where('email', $request->email)
            ->where('is_used', true)
            ->exists();

        if (!$verified) {
            return response()->json([
                'success' => false,
                'message' => 'Email belum diverifikasi. Silakan verifikasi OTP terlebih dahulu.',
            ], 403);
        }

        $review = Review::create([
            'sppg_id' => $request->sppg_id,
            'name'    => $request->name,
            'email'   => $request->email,
            'rating'  => $request->rating,
            'comment' => $request->comment,
            'is_approved' => true, // auto-approve untuk saat ini
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih! Ulasan Anda berhasil dikirim.',
            'review'  => [
                'masked_name' => $review->masked_name,
                'rating'      => $review->rating,
                'comment'     => $review->comment,
            ],
        ]);
    }

    /**
     * Simpan pengajuan SPPG baru.
     * POST /ajukan-sppg
     */
    public function storeSubmission(Request $request)
    {
        $request->validate([
            'nama_pemohon'     => 'required|string|max:255',
            'email_pemohon'    => 'required|email|max:255',
            'no_hp'            => 'required|string|max:20',
            'nama_instansi'    => 'nullable|string|max:255',
            'nama_sppg_usulan' => 'required|string|max:255',
            'alamat'           => 'required|string',
            'provinsi_id'      => 'required|string', // Sesuai dengan form UI
            'kota_id'          => 'required|string', // Sesuai dengan form UI
            'estimasi_sekolah' => 'required|integer|min:1',
            'alasan'           => 'required|string',
            'dokumen_proposal' => 'required|file|mimes:pdf|max:2048', // PDF max 2MB
        ]);

        try {
            // Upload file PDF
            $path = null;
            if ($request->hasFile('dokumen_proposal')) {
                $file = $request->file('dokumen_proposal');
                // Store in storage/app/public/submissions
                $path = $file->store('submissions', 'public');
            }

            // Generate submission number: DRAFT-YYYYMMDD-XXX
            $dateStr = now()->format('Ymd');
            $prefix = "DRAFT-{$dateStr}-";
            
            $lastDraft = SppgDraft::where('submission_number', 'like', "{$prefix}%")
                ->orderBy('submission_number', 'desc')
                ->first();

            $seq = 1;
            if ($lastDraft && preg_match('/-(\d+)$/', $lastDraft->submission_number, $matches)) {
                $seq = (int) $matches[1] + 1;
            }
            $submissionNumber = $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);

            // Auto-geocoding via OpenStreetMap Nominatim
            $latitude = null;
            $longitude = null;
            try {
                $addressQuery = implode(', ', array_filter([
                    $request->alamat,
                    $request->kota_id,
                    $request->provinsi_id,
                ]));

                $url = 'https://nominatim.openstreetmap.org/search?q='
                    . urlencode($addressQuery)
                    . '&format=json&limit=1';

                $res = Http::timeout(5)
                    ->withHeaders(['User-Agent' => 'COMS-MBG-Landing/1.0'])
                    ->get($url);

                if ($res->successful() && !empty($res->json()[0])) {
                    $geo = $res->json()[0];
                    $latitude = (float) $geo['lat'];
                    $longitude = (float) $geo['lon'];
                }
            } catch (\Exception $georefError) {
                Log::warning('Auto geocoding public submission failed: ' . $georefError->getMessage());
            }

            SppgDraft::create([
                'submission_number' => $submissionNumber,
                'submitted_by'      => null,
                'source'            => 'public',
                'form1_data'        => [
                    'name'             => $request->nama_sppg_usulan,
                    'address'          => $request->alamat,
                    'city'             => $request->kota_id,
                    'province'         => $request->provinsi_id,
                    'district'         => null,
                    'capacity'         => (int) $request->estimasi_sekolah,
                    'nama_pemohon'     => $request->nama_pemohon,
                    'email_pemohon'    => $request->email_pemohon,
                    'no_hp'            => $request->no_hp,
                    'nama_instansi'    => $request->nama_instansi,
                    'alasan'           => $request->alasan,
                    'dokumen_proposal' => $path,
                ],
                'form2_data'        => null,
                'form3_data'        => null,
                'latitude'          => $latitude,
                'longitude'         => $longitude,
                'confirmed_latitude'=> null,
                'confirmed_longitude'=> null,
                'point_status'      => null,
                'map_confirmed'     => false,
                'status'            => 'draft',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan berhasil dikirim! Kami akan segera meninjaunya.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses pengajuan. ' . $e->getMessage(),
            ], 500);
        }
    }
}
