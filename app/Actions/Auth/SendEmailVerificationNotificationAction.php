<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Validation\ValidationException;

readonly class SendEmailVerificationNotificationAction
{
    /**
     * Send the email verification notification to the specified user.
     *
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
