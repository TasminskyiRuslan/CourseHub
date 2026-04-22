<?php

namespace App\Actions\Auth;

use App\Data\Auth\Requests\SendPasswordResetEmailData;
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
        $user = User::whereEmail($passwordResetEmailData->email)->first();
        if ($user?->hasRole(UserRole::SUPER_ADMIN->value)) {
            return;
        }

        $status = Password::sendResetLink(['email' => $passwordResetEmailData->email]);
        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }
}
