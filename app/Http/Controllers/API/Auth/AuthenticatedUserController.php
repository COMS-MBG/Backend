<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\AuthUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticatedUserController extends Controller
{
    /**
     * GET /api/auth/user
     *
     * Returns the currently authenticated user with roles & permissions.
     * Used by the frontend to validate session and populate auth state.
     */
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user'    => new AuthUserResource($request->user()),
        ]);
    }
}
