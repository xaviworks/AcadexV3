<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Check if user exists and has 2FA enabled
        $user = User::where('email', $request->email)->first();
        
        if ($user && $user->two_factor_secret && $user->two_factor_confirmed_at) {
            // Store email in session for 2FA verification
            session([
                'password_reset.email' => $request->email,
                'password_reset.requires_2fa' => true,
            ]);
            
            return redirect()->route('password.2fa.challenge');
        }

        // Send reset link for users without 2FA or non-existent users
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
