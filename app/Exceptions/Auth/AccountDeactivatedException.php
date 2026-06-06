<?php

namespace App\Exceptions\Auth;

use Illuminate\Http\JsonResponse;

class AccountDeactivatedException extends \Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Akun tidak aktif',
        ], 403);
    }
}
