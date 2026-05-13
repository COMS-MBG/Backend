<?php

namespace App\Http\Controllers\API\Auth;

use App\Actions\Auth\LoginAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\AuthUserResource;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    /**
     * POST /api/auth/login
     *
     * Authenticates user via session cookie.
     * Flow: validate → rate-limit check → login action → return user.
     */
    public function __invoke(LoginRequest $request, LoginAction $action): JsonResponse
    {
        $request->ensureIsNotRateLimited();

        try {
            $user = $action->execute($request);
        } catch (\Throwable $e) {
            $request->hitRateLimit();
            throw $e;
        }

        $request->clearRateLimit();

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'user'    => new AuthUserResource($user),
        ]);
    }
}