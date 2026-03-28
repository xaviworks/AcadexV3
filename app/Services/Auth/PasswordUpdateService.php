<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordUpdateService
{
    public function __construct(private readonly TrackedSessionService $trackedSessionService)
    {
    }

    public function update(User $user, string $plainPassword, ?string $currentSessionId = null): void
    {
        $user->forceFill([
            'password' => Hash::make($plainPassword),
            'remember_token' => Str::random(60),
        ])->save();

        $this->trackedSessionService->destroyUserSessions($user->id, $currentSessionId);
        $user->devices()->delete();
    }
}
