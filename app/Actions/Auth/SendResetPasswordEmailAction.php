<?php

namespace App\Actions\Auth;

use App\Data\Auth\Requests\ForgotPasswordData;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class SendResetPasswordEmailAction
{
    /**
     * Send an email with a link to reset the password.
     *
     * @param ForgotPasswordData $forgotPasswordData
     * @return void
     * @throws ValidationException
     */
    public function handle(ForgotPasswordData $forgotPasswordData): void
    {
        $user = User::whereEmail($forgotPasswordData->email)->first();
        if ($user?->hasRole(UserRole::SUPER_ADMIN->value)) {
            return;
        }

        $status = Password::sendResetLink(['email' => $forgotPasswordData->email]);
        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }
}
