<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Models\Permission;

class PermissionController extends Controller
{
    // PINTU TARIK — list semua permission (untuk dropdown di FE)
    public function index()
    {
        $permissions = Permission::orderBy('module')
            ->orderBy('feature')
            ->orderBy('action')
            ->get()
            ->groupBy(['module', 'feature']);

        return response()->json($permissions);
    }
}