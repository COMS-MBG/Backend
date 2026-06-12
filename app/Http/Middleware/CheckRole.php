<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Cek apakah user punya role yang dibutuhkan.
     *
     * Pengecekan:
     *   1. role_type (super_admin)
     *   2. employee → role → slug (admin_sppg, ahli_gizi, kurir, dll)
     *
     * Contoh pemakaian di routes:
     *   ->middleware('role:super_admin')
     *   ->middleware('role:admin_sppg,ahli_gizi')
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Super admin bypass semua role gate
        if ($request->user()->isSuperAdmin()) {
            return $next($request);
        }

        // Normalize pipe-separated roles: 'pemilik|manajer|admin-sppg' → ['pemilik','manajer','admin-sppg']
        $normalized = [];
        foreach ($roles as $r) {
            foreach (explode('|', $r) as $s) {
                $normalized[] = trim($s);
            }
        }

        if ($request->user()->hasAnyRole($normalized)) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Akses ditolak. Role tidak sesuai.',
        ], 403);
    }
}
