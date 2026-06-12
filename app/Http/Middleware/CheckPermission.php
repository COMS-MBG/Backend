<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Cek apakah user punya permission yang dibutuhkan.
     *
     * Pengecekan via: user → employee → role → permissions (slug).
     * Super admin bypass semua.
     *
     * Contoh pemakaian di routes:
     *   ->middleware('permission:employee.create')
     *   ->middleware('permission:nutrition.read')
     */
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $user = $request->user();

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Akses ditolak. Permission tidak mencukupi.',
        ], 403);
    }
}
