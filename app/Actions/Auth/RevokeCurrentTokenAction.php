<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;

readonly class RevokeCurrentTokenAction
{
    /**
     * Revoke the current access token for the specified user.
     */
    public function handle(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
