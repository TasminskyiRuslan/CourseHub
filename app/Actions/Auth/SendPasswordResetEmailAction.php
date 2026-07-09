<?php

namespace App\Actions\Auth;

use App\Data\Auth\Requests\SendPasswordResetEmailData;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class SendPasswordResetEmailAction
{
    /**
     * Send an email with a link to reset the password.
     *
     * @param SendPasswordResetEmailData $data
     * @return void
     * @throws ValidationException
     */
    public function handle(SendPasswordResetEmailData $data): void
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
