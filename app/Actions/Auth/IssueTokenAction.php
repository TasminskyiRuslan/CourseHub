<?php

namespace App\Actions\Auth;

use App\DTO\Auth\AuthDTO;
use App\Models\User;

class IssueTokenAction
{
    public function handle(User $user, bool $remember): AuthDTO
    {
        $expiresAt = $remember ? now()->addWeeks(2) : now()->addDay();
        $token = $user->createToken('access_token', ['*'], $expiresAt)->plainTextToken;
        return new AuthDTO($user, $token, $expiresAt);
    }
}
