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
use App\Http\Controllers\API\AdminSPPG\PartnerController;
use App\Http\Controllers\API\AdminSPPG\RoleController;
use App\Http\Controllers\API\AdminSPPG\PermissionController;

Route::middleware(['auth:sanctum'])
    ->prefix('admin-sppg')
    ->group(function () {

    // ── Dashboard ─────────────────────────────────────────────────────────────
    Route::get('dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.read');

    // ── Role Gate: Pemilik, Manajer, Admin SPPG ──────────────────────────────
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
        Route::apiResource('schools', SchoolController::class);

        // RBAC Management
        Route::apiResource('roles', RoleController::class);
        Route::get('permissions', [PermissionController::class, 'index']);

        // ── Partner Management ────────────────────────────────────────────
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
    });

    // ── Manajemen Gizi ────────────────────────────────────────────────────────
    Route::prefix('nutrition')->group(function () {

        // Ingredients
        Route::get('ingredients/dropdown', [IngredientController::class, 'dropdown'])
            ->middleware('permission:ingredients.read');
        Route::post('ingredients/calculate-nutrition', [IngredientController::class, 'calculateNutrition'])
            ->middleware('permission:ingredients.read');
        Route::middleware('permission:ingredients.read')->group(function () {
            Route::get('ingredients', [IngredientController::class, 'index']);
            Route::get('ingredients/{ingredient}', [IngredientController::class, 'show']);
        });
        Route::post('ingredients', [IngredientController::class, 'store'])
            ->middleware('permission:ingredients.create');
        Route::match(['put', 'patch'], 'ingredients/{ingredient}', [IngredientController::class, 'update'])
            ->middleware('permission:ingredients.update');
        Route::delete('ingredients/{ingredient}', [IngredientController::class, 'destroy'])
            ->middleware('permission:ingredients.delete');

        // Recipes
        Route::get('recipes/dropdown', [RecipeController::class, 'dropdown'])
            ->middleware('permission:recipes.read');
        Route::middleware('permission:recipes.read')->group(function () {
            Route::get('recipes', [RecipeController::class, 'index']);
            Route::get('recipes/{recipe}', [RecipeController::class, 'show']);
        });
        Route::post('recipes', [RecipeController::class, 'store'])
            ->middleware('permission:recipes.create');
        Route::match(['put', 'patch'], 'recipes/{recipe}', [RecipeController::class, 'update'])
            ->middleware('permission:recipes.update');
        Route::delete('recipes/{recipe}', [RecipeController::class, 'destroy'])
            ->middleware('permission:recipes.delete');

        // Menus
        Route::post('menus/refresh-statuses', [MenuController::class, 'refreshStatuses'])
            ->middleware('permission:menus.update');
        Route::get('menus/{id}/grouped', [MenuController::class, 'showGrouped'])
            ->middleware('permission:menus.read');
        Route::patch('menus/{id}/publish', [MenuController::class, 'publish'])
            ->middleware('permission:menus.update');
        Route::middleware('permission:menus.read')->group(function () {
            Route::get('menus', [MenuController::class, 'index']);
            Route::get('menus/{menu}', [MenuController::class, 'show']);
        });
        Route::post('menus', [MenuController::class, 'store'])
            ->middleware('permission:menus.create');
        Route::match(['put', 'patch'], 'menus/{menu}', [MenuController::class, 'update'])
            ->middleware('permission:menus.update');
        Route::delete('menus/{menu}', [MenuController::class, 'destroy'])
            ->middleware('permission:menus.delete');
    });

    // ── Distribusi ────────────────────────────────────────────────────────────
    Route::middleware('permission:distribution.read')->group(function () {
        Route::get('distributions', [DistributionController::class, 'index']);
        Route::get('distributions/{distribution}', [DistributionController::class, 'show']);
    });
    Route::post('distributions', [DistributionController::class, 'store'])
        ->middleware('permission:distribution.create');
    Route::match(['put', 'patch'], 'distributions/{distribution}', [DistributionController::class, 'update'])
        ->middleware('permission:distribution.update');
    Route::delete('distributions/{distribution}', [DistributionController::class, 'destroy'])
        ->middleware('permission:distribution.delete');
    Route::post('tracking/update-location', [CourierTrackingController::class, 'updateLocation'])
        ->middleware('permission:distribution.update');
    Route::get('maps/distribution', [DistributionMapController::class, 'index'])
        ->middleware('permission:distribution.read');

    // ── Laporan / Financial Reports ───────────────────────────────────────────
    Route::middleware('permission:report.read')->group(function () {
        Route::get('financial-reports', [FinancialReportController::class, 'index']);
        Route::get('financial-reports/{financial_report}', [FinancialReportController::class, 'show']);
    });
    Route::post('financial-reports', [FinancialReportController::class, 'store'])
        ->middleware('permission:report.create');
    Route::match(['put', 'patch'], 'financial-reports/{financial_report}', [FinancialReportController::class, 'update'])
        ->middleware('permission:report.update');
    Route::delete('financial-reports/{financial_report}', [FinancialReportController::class, 'destroy'])
        ->middleware('permission:report.delete');
});