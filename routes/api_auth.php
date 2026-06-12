<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\LogoutController;
use App\Http\Controllers\API\Auth\AuthenticatedUserController;

/*
|--------------------------------------------------------------------------
| Auth Routes — Cookie-based (Sanctum SPA)
|--------------------------------------------------------------------------
|
| Flow:
| 1. GET  /sanctum/csrf-cookie  → Obtain XSRF-TOKEN (handled by Sanctum)
| 2. POST /api/auth/login       → Authenticate & create session
| 3. GET  /api/auth/user        → Get current user (validate session)
| 4. POST /api/auth/logout      → Destroy session
|
*/

Route::prefix('auth')->middleware('web')->group(function () {

    // Public — no auth required
    Route::post('/login', LoginController::class);

    // Protected — requires authenticated session
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', AuthenticatedUserController::class);
        Route::post('/logout', LogoutController::class);
    });
});