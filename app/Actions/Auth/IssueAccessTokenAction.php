<?php

namespace App\Actions\Auth;

use App\Models\User;
use Laravel\Sanctum\NewAccessToken;

class IssueAccessTokenAction
{
    /**
     * Generates a new personal access token for the user.
     *
     * @param User $user
     * @param bool $remember
     * @return NewAccessToken
     */
    public function handle(User $user, bool $remember = false): NewAccessToken
    {
        $expiresAt = $remember ? now()->addMinutes(config('sanctum.token_ttl.remember')) : now()->addMinutes(config('sanctum.token_ttl.default'));
        return $user->createToken('access_token', ['*'], $expiresAt);
    }
}
