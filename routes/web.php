<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController; 

Route::view('/scalar', 'scalar');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/superadmin/login', function () {
    return view('superadmin.auth.login');
});

// Pastikan URL '/' ini belum dipakai oleh halaman login/dashboard 
Route::get('/', [PublicController::class, 'index'])->name('landing');