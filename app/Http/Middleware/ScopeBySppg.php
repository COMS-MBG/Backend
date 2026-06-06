<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ScopeBySppg
{
    /**
     * Pastikan user hanya bisa akses resource milik SPPG-nya sendiri.
     * Super admin dibebaskan dari pengecekan ini.
     *
     * Contoh pemakaian di routes:
     *   ->middleware('scope.sppg')
     *
     * Route harus punya parameter {sppg} atau {sppg_id}, contoh:
     *   /api/sppg/{sppg}/employees
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $user = $request->user();

        // Super admin boleh akses semua SPPG
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Ambil sppg_id dari route parameter
        $sppgId = $request->route('sppg') 
            ?? $request->route('sppg_id') 
            ?? $request->route('sppgId');

        if ($sppgId && !$user->ownsSppg((int) $sppgId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak punya akses ke SPPG ini.',
            ], 403);
        }

        // Inject sppg_id ke request supaya controller tidak perlu ambil manual
        $request->merge(['_sppg_id' => $user->sppg_id]);

        return $next($request);
    }
}
