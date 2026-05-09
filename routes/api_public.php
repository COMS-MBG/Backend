<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SuperAdmin\RecommendationController;
use App\Http\Controllers\API\SuperAdmin\SPPGSubmissionController;
use App\Http\Controllers\API\SuperAdmin\MonitoringMapController;
use App\Http\Controllers\API\SuperAdmin\DashboardController;
use App\Http\Controllers\API\Public\PublicMenuController;
use App\Http\Controllers\API\Public\PublicMapController;
use App\Http\Controllers\API\Public\FeedbackController;
use App\Http\Controllers\API\Public\RatingController;

Route::prefix('public')->group(function () {

    Route::get(
            'recommendation/generate',
            [RecommendationController::class, 'generate']
        );

        Route::apiResource(
            'sppg-submissions',
            SPPGSubmissionController::class
        );

        Route::get(
            'maps/monitoring',
            [MonitoringMapController::class, 'index']
        );

        Route::get(
            'dashboard',
            [DashboardController::class, 'index']
        );

        Route::get(
        'menus',
        [PublicMenuController::class, 'index']
    );

    Route::get(
        'maps/sppg',
        [PublicMapController::class, 'index']
    );

    Route::post(
        'feedback',
        [FeedbackController::class, 'store']
    );

    Route::post(
        'rating',
        [RatingController::class, 'store']
    );
});