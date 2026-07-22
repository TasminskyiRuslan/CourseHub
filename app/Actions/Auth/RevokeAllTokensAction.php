<?php

namespace App\Actions\Auth;

use App\Models\User;

readonly class RevokeAllTokensAction
{
    /**
     * Revoke all access tokens for the specified user.
     *
     * @param User $user
     * @return void
     */
    public function handle(User $user): void
    {
        $user->tokens()->delete();
    }
}
