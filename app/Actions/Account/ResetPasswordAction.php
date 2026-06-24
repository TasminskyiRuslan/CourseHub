<?php

namespace App\Actions\Account;

use App\Data\Account\Requests\ResetPasswordData;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Str;

class ResetPasswordAction
{
    /**
     * Reset the user's password.
     *
     * @param ResetPasswordData $resetPasswordData
     * @return void
     */
    public function handle(ResetPasswordData $resetPasswordData): void
    {
        $status = Password::reset([
            'email' => $resetPasswordData->email,
            'password' => $resetPasswordData->password,
            'password_confirmation' => $resetPasswordData->password,
            'token' => $resetPasswordData->token,
        ], function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }
    }
}
