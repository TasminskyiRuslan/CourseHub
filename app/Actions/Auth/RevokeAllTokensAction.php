<?php

namespace App\Actions\Auth;

use App\Models\User;

class RevokeAllTokensAction
{
    public function handle(User $user): void
    {
        $user->tokens()->delete();
    }
}
