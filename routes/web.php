<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController; 

use App\Http\Controllers\LandingController;

Route::view('/scalar', 'scalar');


Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/superadmin/login', function () {
    return view('superadmin.auth.login');
});

// Route to New Isolated Landing Page
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/feedback', [PublicController::class, 'storeFeedback'])->name('feedback.store');

// ── Review dengan Verifikasi OTP ──
Route::post('/review/send-otp',   [LandingController::class, 'sendOtp'])->name('review.sendOtp');
Route::post('/review/verify-otp', [LandingController::class, 'verifyOtp'])->name('review.verifyOtp');
Route::post('/review/store',      [LandingController::class, 'storeReview'])->name('review.store');

// ── Pengajuan SPPG Baru ──
Route::post('/ajukan-sppg', [LandingController::class, 'storeSubmission'])->name('sppg.submit');