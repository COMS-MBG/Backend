<?php

namespace App\Exceptions\Auth;

use Illuminate\Http\JsonResponse;

class AccountDeactivatedException extends \Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
        ], 403);
    }
}
