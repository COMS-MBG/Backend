<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ScopeBySppg
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $user = $request->user();

        // Superadmin persis kayak kode asli lu, nggak diubah
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // PERBAIKAN: Ambil ID dari database user/employee, BUKAN dari URL
        $sppgId = $user->sppg_id ?? $user->employee?->sppg_id;

        if (!$sppgId) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak punya akses ke SPPG ini.',
            ], 403);
        }

        // Inject ke request agar bisa dipakai di Controller
        $request->attributes->set('sppg_id', (int) $sppgId);

        return $next($request);
    }
}