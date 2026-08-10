<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Data\Auth\Requests\SendPasswordResetLinkData;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

readonly class SendPasswordResetLinkAction
{
    /**
     * Send a password reset link to the user identified by the email.
     *
     * @throws ValidationException
     */
    public function handle(SendPasswordResetLinkData $data): void
    {
        $resetStatus = Password::sendResetLink(['email' => $data->email]);

        if ($resetStatus === Password::INVALID_USER) {
            return;
        }

        if ($resetStatus !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($resetStatus)],
            ]);
        }
    }
}
