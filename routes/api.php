<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/api_auth.php';
require __DIR__.'/api_superadmin.php';
require __DIR__.'/api_adminsppg.php';
require __DIR__.'/api_public.php';

/*
 * ═══════════════════════════════════════════════════════════════
 *  DISTRIBUTION MODULE
 * ═══════════════════════════════════════════════════════════════
 * BUG FIX: sebelumnya tidak ada auth middleware → semua endpoint
 * bisa diakses tanpa login. Sekarang dibungkus auth:sanctum.
 *
 * FULL BASE URL: /api/distribution/...
 * ═══════════════════════════════════════════════════════════════
 */
Route::middleware(['auth:sanctum'])
    ->prefix('distribution')
    ->group(base_path('routes/distribution.php'));