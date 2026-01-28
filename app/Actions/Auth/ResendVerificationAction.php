<?php

namespace App\Actions\Auth;

use App\Models\User;

class ResendVerificationAction
{
    public function handle(User $user): void
    {
        if (!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
    }
}
