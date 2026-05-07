<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AdminSPPG\EmployeeController;
use App\Http\Controllers\API\AdminSPPG\SchoolController;
use App\Http\Controllers\API\AdminSPPG\IngredientController;
use App\Http\Controllers\API\AdminSPPG\RecipeController;
use App\Http\Controllers\API\AdminSPPG\MenuController;
use App\Http\Controllers\API\AdminSPPG\DistributionController;
use App\Http\Controllers\API\AdminSPPG\CourierTrackingController;
use App\Http\Controllers\API\AdminSPPG\DashboardController;
use App\Http\Controllers\API\AdminSPPG\DistributionMapController;
use App\Http\Controllers\API\AdminSPPG\FinancialReportController;
use App\Http\Controllers\API\AdminSPPG\RoleController;       // ← tambahan baru
use App\Http\Controllers\API\AdminSPPG\PermissionController; // ← tambahan baru

Route::middleware(['au:sanctum'])
    ->prefix('admin-sppg')
    ->group(function () {

    // ── Khusus Pemilik & Manajer ──────────────────────────────────────────────
    Route::middleware('role:pemilik|manajer|admin-sppg')->group(function () {
        Route::apiResource('employees', EmployeeController::class);
        Route::apiResource('schools', SchoolController::class);

        // Role & Permission — hanya pemilik & manajer yang boleh kelola
        Route::get('employees/{employee}/assign-role', [EmployeeController::class, 'showAssignRole']);
        Route::post('employees/{employee}/assign-role', [EmployeeController::class, 'assignRole']);
        Route::apiResource('roles', RoleController::class);
        Route::get('permissions', [PermissionController::class, 'index']); // list semua permission
    });

    // ── Semua Admin SPPG (tanpa role restriction) ─────────────────────────────

    Route::get('dashboard', [DashboardController::class, 'index']);

    // Modul Manajemen Gizi
    Route::prefix('nutrition')->group(function () {

        // Master Data Bahan Baku
        Route::get('ingredients/dropdown', [IngredientController::class, 'dropdown']);
        Route::post('ingredients/calculate-nutrition', [IngredientController::class, 'calculateNutrition']);
        Route::apiResource('ingredients', IngredientController::class);

        // Master Resep
        Route::get('recipes/dropdown', [RecipeController::class, 'dropdown']);
        Route::apiResource('recipes', RecipeController::class);

        // Perencanaan Menu
        Route::post('menus/refresh-statuses', [MenuController::class, 'refreshStatuses']);
        Route::get('menus/{id}/grouped', [MenuController::class, 'showGrouped']);
        Route::patch('menus/{id}/publish', [MenuController::class, 'publish']);
        Route::apiResource('menus', MenuController::class);

    });

    Route::apiResource('distributions', DistributionController::class);
    Route::post('tracking/update-location', [CourierTrackingController::class, 'updateLocation']);
    Route::apiResource('financial-reports', FinancialReportController::class);
    Route::get('maps/distribution', [DistributionMapController::class, 'index']);

});