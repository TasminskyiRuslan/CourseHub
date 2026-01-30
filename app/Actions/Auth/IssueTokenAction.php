<?php

namespace App\Actions\Auth;

use App\Data\Auth\AuthData;
use App\Models\User;

class IssueTokenAction
{
    public function handle(User $user, bool $remember): AuthData
    {
        $expiresAt = $remember ? now()->addWeeks(2) : now()->addDay();
        $token = $user->createToken('access_token', ['*'], $expiresAt)->plainTextToken;
        return new AuthData($user, $token, $expiresAt);
    }
}
