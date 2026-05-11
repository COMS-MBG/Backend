<?php

namespace App\Exceptions\Auth;

use Illuminate\Http\JsonResponse;

class InvalidCredentialsException extends \Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Email atau password salah.',
        ], 401);
    }
}
