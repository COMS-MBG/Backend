<?php

use Illuminate\Support\Facades\Route;

use App\Constants\RoleConstant;
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
use App\Http\Controllers\API\AdminSPPG\OperationalReportController;
use App\Http\Controllers\API\AdminSPPG\PartnerController;
use App\Http\Controllers\API\AdminSPPG\RoleController;
use App\Http\Controllers\API\AdminSPPG\PermissionController;
use App\Http\Controllers\API\AdminSPPG\StockController;

// FIX: Tambahkan 'scope.sppg' di sini agar SEMUA route di dalam group ini terisolasi datanya
Route::middleware(['auth:sanctum', 'scope.sppg'])
    ->prefix('admin-sppg')
    ->group(function () {

    // ── Dashboard ─────────────────────────────────────────────────────────────
    Route::get('dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.read');

    // ── Role Gate: Pemilik, Admin SPPG (Manager dihapus dari RoleConstant) ────
    Route::middleware('role:' . RoleConstant::SPPG_MANAGEMENT_ROLES)->group(function () {

        // ── Employee Management ───────────────────────────────────────────
        Route::middleware('permission:employee.read')->group(function () {
            Route::get('employees', [EmployeeController::class, 'index']);
            Route::get('employees/{employee}', [EmployeeController::class, 'show']);
        });
        Route::post('employees', [EmployeeController::class, 'store'])
            ->middleware('permission:employee.create');
        Route::match(['put', 'patch'], 'employees/{employee}', [EmployeeController::class, 'update'])
            ->middleware('permission:employee.update');
        Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])
            ->middleware('permission:employee.delete');

        // Role & Permission assignment
        Route::get('employees/{employee}/assign-role', [EmployeeController::class, 'showAssignRole'])
            ->middleware('permission:employee.read');
        Route::post('employees/{employee}/assign-role', [EmployeeController::class, 'assignRole'])
            ->middleware('permission:employee.update');

        // School Management
        Route::name('sppg.')->group(function () {
            Route::apiResource('schools', SchoolController::class);
            Route::apiResource('roles', RoleController::class);
        });
        Route::get('permissions', [PermissionController::class, 'index']);
    });

    // ── Partner Management ────────────────────────────────────────────────────
    Route::get('partners/summary', [PartnerController::class, 'summary'])
        ->middleware('permission:partner.read');
    Route::post('partners/import', [PartnerController::class, 'import'])
        ->middleware('permission:partner.create');
    Route::middleware('permission:partner.read')->group(function () {
        Route::get('partners', [PartnerController::class, 'index']);
        Route::get('partners/{partner}', [PartnerController::class, 'show']);
    });
    Route::post('partners', [PartnerController::class, 'store'])
        ->middleware('permission:partner.create');
    Route::match(['put', 'patch'], 'partners/{partner}', [PartnerController::class, 'update'])
        ->middleware('permission:partner.update');
    Route::delete('partners/{partner}', [PartnerController::class, 'destroy'])
        ->middleware('permission:partner.delete');

    // ── Nutrition Management ──────────────────────────────────────────────────
    // Middleware dihapus karena sudah di-handle di dalam Controller masing-masing
    Route::prefix('nutrition')->group(function () {
        // Ingredients
        Route::get('ingredients/dropdown', [IngredientController::class, 'dropdown']);
        Route::post('ingredients/calculate-nutrition', [IngredientController::class, 'calculateNutrition']);
        Route::get('ingredients', [IngredientController::class, 'index']);
        Route::get('ingredients/{ingredient}', [IngredientController::class, 'show']);
        Route::post('ingredients', [IngredientController::class, 'store']);
        Route::match(['put', 'patch'], 'ingredients/{ingredient}', [IngredientController::class, 'update']);
        Route::delete('ingredients/{ingredient}', [IngredientController::class, 'destroy']);

        // Recipes
        Route::get('recipes/dropdown', [RecipeController::class, 'dropdown']);
        Route::get('recipes', [RecipeController::class, 'index']);
        Route::get('recipes/{recipe}', [RecipeController::class, 'show']);
        Route::post('recipes', [RecipeController::class, 'store']);
        Route::match(['put', 'patch'], 'recipes/{recipe}', [RecipeController::class, 'update']);
        Route::delete('recipes/{recipe}', [RecipeController::class, 'destroy']);

        // Menus
        Route::post('menus/refresh-statuses', [MenuController::class, 'refreshStatuses']);
        Route::get('menus/{id}/grouped', [MenuController::class, 'showGrouped']);
        Route::patch('menus/{id}/publish', [MenuController::class, 'publish']);
        Route::get('menus', [MenuController::class, 'index']);
        Route::get('menus/{menu}', [MenuController::class, 'show']);
        Route::post('menus', [MenuController::class, 'store']);
        Route::match(['put', 'patch'], 'menus/{menu}', [MenuController::class, 'update']);
        Route::delete('menus/{menu}', [MenuController::class, 'destroy']);
    });

    // ── Manajemen Stok ────────────────────────────────────────────────────────
    Route::prefix('stocks')->group(function () {
        Route::get('/', [StockController::class, 'index']);
        Route::get('/pending', [StockController::class, 'pendingApproval']);
        Route::get('/transactions', [StockController::class, 'allTransactions']);
        Route::get('/check-menu/{menu_id}', [StockController::class, 'checkMenu']);
        Route::get('/{ingredient_id}', [StockController::class, 'show']);
        Route::post('/', [StockController::class, 'store']);
        Route::put('/minimum/{ingredient_id}', [StockController::class, 'updateMinimum']);
        Route::put('/{id}', [StockController::class, 'update']);
        Route::delete('/{id}', [StockController::class, 'destroy']);
        Route::post('/{id}/approve', [StockController::class, 'approve']);
        Route::post('/{id}/reject', [StockController::class, 'reject']);
        Route::get('/{id}/transactions', [StockController::class, 'batchTransactions']);
    });

    // ── Distribusi ────────────────────────────────────────────────────────────
    // List & detail jadwal (admin SPPG bisa lihat semua, kurir hanya miliknya)
    Route::middleware('permission:distribution.read')->group(function () {
        Route::get('distributions',                [DistributionController::class, 'index']);
        Route::get('distributions/{distribution}', [DistributionController::class, 'show']);
    });
    // Submit tugas ke kurir (Admin SPPG action)
    Route::post('distributions/submit', [DistributionController::class, 'store'])
        ->middleware('permission:distribution.create');

    // ── Courier Tracking (REST fallback – gunakan Reverb untuk real-time) ──────
    Route::post('tracking/update-location',      [CourierTrackingController::class, 'updateLocation'])
        ->middleware('permission:distribution.update');
    Route::get('tracking/active',                [CourierTrackingController::class, 'activeCouriers'])
        ->middleware('permission:distribution.read');
    Route::get('tracking/{scheduleId}/trail',    [CourierTrackingController::class, 'trail'])
        ->middleware('permission:distribution.read');

    // ── Peta Distribusi ────────────────────────────────────────────────────────
    Route::get('maps/distribution', [DistributionMapController::class, 'index'])
        ->middleware('permission:distribution.read');

    // ── Laporan Operasional ────────────────────────────────────────────────────
    Route::get('reports/operational', [OperationalReportController::class, 'index'])
        ->middleware('permission:report.read');

    // ── Laporan Keuangan (Delivery Cost) ──────────────────────────────────────
    Route::prefix('reports/financial')->group(function () {
        Route::get('/',                         [FinancialReportController::class, 'index'])
            ->middleware('permission:report.read');
        Route::get('/rates',                    [FinancialReportController::class, 'rates'])
            ->middleware('permission:report.read');
        Route::put('/rates/{vehicleType}',      [FinancialReportController::class, 'updateRate'])
            ->middleware('permission:report.update');
    });
});