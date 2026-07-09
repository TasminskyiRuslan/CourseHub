<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class SendVerificationEmailAction
{
    /**
     * Send the email verification notification to the user.
     *
     * @param User $user
     * @return void
     * @throws ValidationException
     */
    public function handle(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
        throw ValidationException::withMessages([
            'email' => [__('auth.verified')],
        ]);
    }

        $user->sendEmailVerificationNotification();
    }
}
