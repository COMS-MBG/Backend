<?php

namespace App\Exceptions\Auth;

use Illuminate\Http\JsonResponse;

class SppgInactiveException extends \Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Menunggu aktivasi SPPG. Hubungi pemilik SPPG untuk melakukan login pertama.',
        ], 403);
    }
}
