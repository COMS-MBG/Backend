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
 * HOW TO REGISTER:
 *   In config/app.php, add to 'providers' array:
 *   App\Providers\DistributionServiceProvider::class,
 *
 *   Or in bootstrap/providers.php (Laravel 11+):
 *   App\Providers\DistributionServiceProvider::class,
 */
class DistributionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeliveryScheduleService::class);
        $this->app->singleton(RouteOptimizationService::class);
        $this->app->singleton(CourierLocationService::class);

        // Merge module config
        $this->mergeConfigFrom(__DIR__ . '/../../config/distribution.php', 'distribution');
    }

    public function boot(): void
    {
        // Load module routes
        $this->loadRoutesFrom(base_path('routes/distribution.php'));
    }
}