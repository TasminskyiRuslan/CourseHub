<?php

namespace App\Actions\Auth;

use App\Models\User;

readonly class RevokeCurrentTokenAction
{
    /**
     * Revoke the current access token for the specified user.
     *
     * @param User $user
     * @return void
     */
    public function handle(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
