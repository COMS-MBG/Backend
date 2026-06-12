<?php

namespace App\Providers;

use App\Services\Distribution\CourierLocationService;
use App\Services\Distribution\DeliveryScheduleService;
use App\Services\Distribution\RouteOptimizationService;
use Illuminate\Support\ServiceProvider;

/**
 * Distribution Module Service Provider
 *
 * FILE LOCATION: app/Providers/DistributionServiceProvider.php
 *
 * ROUTE LOADING:
 *   Routes di-load via routes/api.php (dibungkus auth:sanctum + prefix 'distribution').
 *   JANGAN panggil loadRoutesFrom() di sini — akan menyebabkan routes terdaftar dua kali
 *   (sekali tanpa prefix via ServiceProvider, sekali dengan prefix via api.php).
 *
 * REGISTRASI:
 *   Daftarkan di bootstrap/providers.php:
 *   App\Providers\DistributionServiceProvider::class,
 */
class DistributionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Singleton bindings — DI container resolve sekali, reuse instance
        $this->app->singleton(DeliveryScheduleService::class);
        $this->app->singleton(RouteOptimizationService::class);
        $this->app->singleton(CourierLocationService::class);

        // Merge module config dari config/distribution.php
        $this->mergeConfigFrom(__DIR__ . '/../../config/distribution.php', 'distribution');
    }

    public function boot(): void
    {
        // Routes TIDAK di-load di sini.
        // Sudah di-handle oleh routes/api.php dengan:
        //   Route::middleware(['auth:sanctum'])
        //       ->prefix('distribution')
        //       ->group(base_path('routes/distribution.php'));
    }
}