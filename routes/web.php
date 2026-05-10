<?php

use Illuminate\Support\Facades\Route;

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