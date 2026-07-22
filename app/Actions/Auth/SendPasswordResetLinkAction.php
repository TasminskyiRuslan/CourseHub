<?php

namespace App\Actions\Auth;

use App\Data\Auth\Requests\SendPasswordResetLinkData;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

readonly class SendPasswordResetLinkAction
{
    /**
     * Send a password reset link to the user identified by the email.
     *
     * @param SendPasswordResetLinkData $data
     * @return void
     * @throws ValidationException
     */
    public function handle(SendPasswordResetLinkData $data): void
    {
        $status = Password::sendResetLink(['email' => $data->email]);

        if ($status === Password::INVALID_USER) {
            return;
        }

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }
}
