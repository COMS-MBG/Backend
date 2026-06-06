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

        $token = $user->createToken('auth_token')->plainTextToken;

        $responseData = [
            'success' => true,
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => new AuthUserResource($user),
        ];

        if ($user->role_type === 'sppg_user') {
            $user->loadMissing(['employee.role.permissions', 'sppg']);
            $responseData['sppg_status'] = $user->sppg?->status;
            $responseData['permissions'] = $user->employee?->role?->permissions?->pluck('slug')->toArray() ?? [];
        }

        return response()->json($responseData);
    }
}