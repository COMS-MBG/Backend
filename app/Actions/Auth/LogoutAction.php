<?php

namespace App\Actions\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutAction
{
    /**
     * Destroy the authenticated session securely.
     *
     * Steps:
     * 1. Logout the user from the web guard
     * 2. Invalidate the entire session (removes all data)
     * 3. Regenerate CSRF token (prevents token reuse)
     */
    public function execute(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
