<?php

use App\Http\Controllers\Api\Distribution\DeliveryHistoryController;
use App\Http\Controllers\Api\Distribution\DeliveryScheduleController;
use App\Http\Controllers\Api\Distribution\SpatialMapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Distribution Management API Routes
|--------------------------------------------------------------------------
|
| FILE LOCATION : routes/distribution.php
|
| HOW TO REGISTER:
|   In routes/api.php, add:
|
|   Route::middleware(['auth:sanctum'])->prefix('distribution')->group(
|       base_path('routes/distribution.php')
|   );
|
|--------------------------------------------------------------------------
| FULL BASE URL : /api/distribution
|--------------------------------------------------------------------------
*/

// ═══════════════════════════════════════════════════════════════
//  DELIVERY SCHEDULE (Fitur Jadwal Pengiriman)
// ═══════════════════════════════════════════════════════════════

Route::prefix('schedules')->group(function () {

    // PINTU KELUAR – list & detail
    Route::get('/',            [DeliveryScheduleController::class, 'index'])->name('distribution.schedules.index');
    Route::get('/{schedule}',  [DeliveryScheduleController::class, 'show'])->name('distribution.schedules.show');

    // PINTU KELUAR – helpers
    Route::get('/meta/couriers', [DeliveryScheduleController::class, 'availableCouriers'])->name('distribution.schedules.couriers');

    // PINTU MASUK – Admin Logistik CRUD
    Route::post('/',           [DeliveryScheduleController::class, 'store'])->name('distribution.schedules.store');
    Route::put('/{schedule}',  [DeliveryScheduleController::class, 'update'])->name('distribution.schedules.update');
    Route::delete('/{schedule}', [DeliveryScheduleController::class, 'destroy'])->name('distribution.schedules.destroy');

    // PINTU MASUK – Admin SPPG: submit task to courier
    Route::post('/{schedule}/submit',           [DeliveryScheduleController::class, 'submitTask'])->name('distribution.schedules.submit');

    // PINTU MASUK – Courier workflow
    Route::post('/{schedule}/accept',           [DeliveryScheduleController::class, 'acceptTask'])->name('distribution.schedules.accept');
    Route::post('/{schedule}/reject',           [DeliveryScheduleController::class, 'rejectTask'])->name('distribution.schedules.reject');
    Route::post('/{schedule}/proof',            [DeliveryScheduleController::class, 'submitProof'])->name('distribution.schedules.proof');
    Route::post('/{schedule}/proof/resubmit',   [DeliveryScheduleController::class, 'resubmitProof'])->name('distribution.schedules.proof.resubmit');

    // PINTU MASUK – Admin Logistik: confirm or request revision
    Route::post('/{schedule}/confirm',          [DeliveryScheduleController::class, 'confirmDelivery'])->name('distribution.schedules.confirm');
    Route::post('/{schedule}/revision',         [DeliveryScheduleController::class, 'requestRevision'])->name('distribution.schedules.revision');
});

// ═══════════════════════════════════════════════════════════════
//  DELIVERY HISTORY (Fitur Riwayat Pengiriman)
// ═══════════════════════════════════════════════════════════════

Route::prefix('histories')->group(function () {

    // PINTU KELUAR
    Route::get('/',            [DeliveryHistoryController::class, 'index'])->name('distribution.histories.index');
    Route::get('/analytics',   [DeliveryHistoryController::class, 'analytics'])->name('distribution.histories.analytics');
    Route::get('/{history}',   [DeliveryHistoryController::class, 'show'])->name('distribution.histories.show');
});

// ═══════════════════════════════════════════════════════════════
//  SPATIAL MAP & ANALYTICS (Fitur Peta Spasial)
// ═══════════════════════════════════════════════════════════════

Route::prefix('map')->group(function () {

    // PINTU KELUAR – admin live map
    Route::get('/active-couriers', [SpatialMapController::class, 'activeCouriers'])->name('distribution.map.active');
    Route::get('/depot',           [SpatialMapController::class, 'depotLocation'])->name('distribution.map.depot');
    Route::get('/trail/{schedule}',[SpatialMapController::class, 'locationTrail'])->name('distribution.map.trail');

    // PINTU MASUK – courier GPS ping (high frequency, keep lightweight)
    Route::post('/location/{schedule}', [SpatialMapController::class, 'recordLocation'])->name('distribution.map.location');

    // PINTU MASUK/KELUAR – route optimization request
    Route::post('/optimize-route', [SpatialMapController::class, 'optimizeRoute'])->name('distribution.map.optimize');
});