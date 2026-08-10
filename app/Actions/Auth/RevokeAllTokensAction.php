<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;

readonly class RevokeAllTokensAction
{
    /**
     * Revoke all access tokens for the specified user.
     */
    public function handle(User $user): void
    {
        $user->tokens()->delete();
    }
}
