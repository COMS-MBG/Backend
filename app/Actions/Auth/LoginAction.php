<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Exceptions\Auth\AccountDeactivatedException;

class LoginAction
{
    /**
     * Authenticate user and create a session.
     *
     * Steps:
     * 1. Find user by email
     * 2. Verify password
     * 3. Check is_active
     * 4. Create authenticated session
     * 5. Regenerate session ID (prevents session fixation)
     *
     * @throws InvalidCredentialsException
     * @throws AccountDeactivatedException
     */
    public function execute(Request $request): User
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw new InvalidCredentialsException();
        }

        if (!$user->is_active) {
            throw new AccountDeactivatedException();
        }

        // Create authenticated session via web guard
        $remember = $request->boolean('remember');
        Auth::login($user, $remember);

        // Regenerate session ID to prevent session fixation attacks
        $request->session()->regenerate();

        return $user;
    }
}
