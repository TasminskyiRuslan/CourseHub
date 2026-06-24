<?php

namespace App\Actions\Account;

use App\Data\Account\Requests\SendPasswordResetEmailData;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class SendResetPasswordEmailAction
{
    /**
     * Send an email with a link to reset the password.
     *
     * @param SendPasswordResetEmailData $passwordResetEmailData
     * @return void
     * @throws ValidationException
     */
    public function handle(SendPasswordResetEmailData $passwordResetEmailData): void
    {
        $status = Password::sendResetLink(['email' => $passwordResetEmailData->email]);
        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }
}
