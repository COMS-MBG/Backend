<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\Middleware\RoleMiddleware as SpatieRoleMiddleware;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        if (!$request->user()->hasAnyRole($roles)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Role tidak sesuai.'], 403);
        }

        return $next($request);
    }
}