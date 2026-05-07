<?php

use App\Http\Controllers\API\SuperAdmin\EmployeeController;
use App\Http\Controllers\API\SuperAdmin\SchoolController;
use App\Http\Controllers\API\SuperAdmin\SPPGController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SuperAdmin\FinancialReportController;


/*
|--------------------------------------------------------------------------
| Super Admin Routes — Phase 2
| Prefix  : /api/super-admin
| Middleware: auth:sanctum + role:super_admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:super_admin'])
    ->prefix('super-admin')
    ->group(function () {

    // ── SPPG ───────────────────────────────────────────────────────────────────
    Route::prefix('sppg')->group(function () {
        Route::get('/',                           [SPPGController::class, 'index']);
        Route::post('/',                          [SPPGController::class, 'store']);
        Route::get('/capacity-overview',          [SPPGController::class, 'capacityOverview']);
        Route::get('/{id}',                       [SPPGController::class, 'show']);
        Route::put('/{id}',                       [SPPGController::class, 'update']);
        Route::delete('/{id}',                    [SPPGController::class, 'destroy']);
        Route::post('/{sppgId}/assign-school',    [SPPGController::class, 'assignSchool']);
        Route::delete('/{sppgId}/schools/{schoolId}', [SPPGController::class, 'detachSchool']);

        // Karyawan per SPPG
        Route::get('/{sppgId}/employees',         [EmployeeController::class, 'index']);
        Route::post('/{sppgId}/employees',        [EmployeeController::class, 'store']);
        Route::get('/{sppgId}/employees/{id}',    [EmployeeController::class, 'show']);
        Route::put('/{sppgId}/employees/{id}',    [EmployeeController::class, 'update']);
        Route::delete('/{sppgId}/employees/{id}', [EmployeeController::class, 'destroy']);
    });

    // ── Schools ────────────────────────────────────────────────────────────────
    Route::apiResource('schools', SchoolController::class);

    Route::apiResource(
            'financial-reports',
            FinancialReportController::class
        );
});