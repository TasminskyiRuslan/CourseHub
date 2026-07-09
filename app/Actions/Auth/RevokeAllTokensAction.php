<?php

namespace App\Actions\Auth;

use App\Models\User;

class RevokeAllTokensAction
{
    /**
     * Revoke all access tokens for an authenticated user.
     *
     * @param User $user
     * @return void
     */
    public function handle(User $user): void
    {
        $user->tokens()->delete();
    }
}
