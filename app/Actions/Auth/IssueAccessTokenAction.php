<?php

namespace App\Actions\Auth;

use App\Models\User;
use Laravel\Sanctum\NewAccessToken;

readonly class IssueAccessTokenAction
{
    /**
     * Issue a new personal access token for the specified user.
     *
     * @param User $user
     * @param bool $remember
     * @return NewAccessToken
     */
    public function handle(User $user, bool $remember = false): NewAccessToken
    {
        $ttl = $remember
            ? config('sanctum.token_ttl.remember')
            : config('sanctum.token_ttl.default');

        return $user->createToken(
            name: 'access_token',
            expiresAt: now()->addMinutes($ttl)
        );
    }
}
