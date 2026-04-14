<?php

namespace App\Actions\Auth;

use App\Models\User;

class ResendVerificationEmailAction
{
    /**
     * Resend the email verification notification to the authenticated user.
     *
     * @param User $user
     * @return void
     */
    public function handle(User $user): void
    {
        if (!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
    }
}
