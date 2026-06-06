<?php

use App\Http\Controllers\API\SuperAdmin\EmployeeController;
use App\Http\Controllers\API\SuperAdmin\SchoolController;
use App\Http\Controllers\API\SuperAdmin\SPPGController;
use App\Http\Controllers\API\SuperAdmin\FinancialReportController;
use App\Http\Controllers\API\SuperAdmin\DashboardController;
use App\Http\Controllers\API\SuperAdmin\SppgSubmissionController;
use App\Http\Controllers\API\SuperAdmin\MapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin Routes
| Prefix  : /api/super-admin
| Middleware: auth:sanctum + role:super_admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:super_admin'])
    ->prefix('super-admin')
    ->group(function () {

    // ── Dashboard ──────────────────────────────────────────────────────────────
    Route::get('/dashboard',                      [DashboardController::class, 'index']);

    // ── SPPG ───────────────────────────────────────────────────────────────────
    Route::prefix('sppg')->group(function () {
        Route::get('/',                           [SPPGController::class, 'index']);
        Route::post('/',                          [SPPGController::class, 'store']);
        Route::get('/capacity-overview',          [SPPGController::class, 'capacityOverview']);

        // ── Region dependent dropdowns ──────────────────────────────────────
        Route::get('/regions/cities',             [SPPGController::class, 'regionCities']);
        Route::get('/regions/districts',          [SPPGController::class, 'regionDistricts']);

        // ── Wildcard routes (must be after static routes above) ─────────────
        Route::get('/{id}',                       [SPPGController::class, 'show']);
        Route::put('/{id}',                       [SPPGController::class, 'update']);
        Route::delete('/{id}',                    [SPPGController::class, 'destroy']);
        Route::post('/{sppgId}/assign-school',    [SPPGController::class, 'assignSchool']);
        Route::delete('/{sppgId}/schools/{schoolId}', [SPPGController::class, 'detachSchool']);

        // Activation, partners, and menus
        Route::post('/{id}/deactivate',           [SPPGController::class, 'deactivate']);
        Route::post('/{id}/activate',             [SPPGController::class, 'activate']);
        Route::get('/{id}/partners',              [SPPGController::class, 'partners']);
        Route::get('/{id}/menus',                 [SPPGController::class, 'menus']);

        // Employees per SPPG
        Route::get('/{sppgId}/employees',         [EmployeeController::class, 'index']);
        Route::post('/{sppgId}/employees',        [EmployeeController::class, 'store']);
        Route::get('/{sppgId}/employees/{id}',    [EmployeeController::class, 'show']);
        Route::put('/{sppgId}/employees/{id}',    [EmployeeController::class, 'update']);
        Route::delete('/{sppgId}/employees/{id}', [EmployeeController::class, 'destroy']);
    });

    // ── SPPG Submissions (Drafts) ──────────────────────────────────────────────
    Route::prefix('sppg-submissions')->group(function () {
        Route::get('/',                           [SppgSubmissionController::class, 'index']);
        Route::post('/',                          [SppgSubmissionController::class, 'store']);
        Route::get('/{id}',                       [SppgSubmissionController::class, 'show']);
        Route::put('/{id}',                       [SppgSubmissionController::class, 'update']);
        Route::delete('/{id}',                    [SppgSubmissionController::class, 'destroy']);
        Route::post('/{id}/submit',               [SppgSubmissionController::class, 'submit']);
    });

    // ── GIS Maps & Analytics ───────────────────────────────────────────────────
    Route::prefix('map')->group(function () {
    Route::get('/data',                                   [MapController::class, 'getMapData']);
    Route::post('/geocode',                               [MapController::class, 'geocode']);
    Route::post('/route-check',                           [MapController::class, 'routeCheck']);
    Route::post('/validate-point',                        [MapController::class, 'validatePoint']);
    Route::post('/suggest-shift',                         [MapController::class, 'suggestShift']);
    Route::post('/confirm-point/{submission_id}',         [MapController::class, 'confirmPoint']);
});

    // ── Schools ────────────────────────────────────────────────────────────────
    Route::apiResource('schools', SchoolController::class);

    // ── Financial Reports ──────────────────────────────────────────────────────
    Route::apiResource('financial-reports', FinancialReportController::class);
});