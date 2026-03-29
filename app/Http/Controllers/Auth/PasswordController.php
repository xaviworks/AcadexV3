<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\SecurityAlert;
use App\Services\Auth\PasswordUpdateService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordController extends Controller
{
    public function __construct(private readonly PasswordUpdateService $passwordUpdateService)
    {
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                PasswordRule::min(8)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols(),
                'confirmed',
            ],
        ]);

        $user = $request->user();

        $this->passwordUpdateService->update(
            $user,
            $validated['password'],
            $request->session()->getId()
        );

        NotificationService::notifySecurityAlert(
            SecurityAlert::TYPE_PASSWORD_CHANGED,
            $user,
            null,
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return back()->with('status', 'password-updated');
    }
}
