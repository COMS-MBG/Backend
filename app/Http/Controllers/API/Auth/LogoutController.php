<?php

namespace App\Http\Controllers\API\Auth;

use App\Actions\Auth\LogoutAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    /**
     * POST /api/auth/logout
     *
     * Destroys the authenticated session, invalidates cookies,
     * and regenerates the CSRF token.
     */
    public function __invoke(Request $request, LogoutAction $action): JsonResponse
    {
        $action->execute($request);

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }
}
