<?php

namespace App\Actions\Auth;

use App\Data\Auth\Requests\ResetPasswordData;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ResetPasswordAction
{
    /**
     * Reset the password for the user identified by the email.
     *
     * @param ResetPasswordData $data
     * @return void
     * @throws ValidationException
     */
    public function handle(ResetPasswordData $data): void
    {
        $status = Password::reset([
            'email' => $data->email,
            'password' => $data->password,
            'password_confirmation' => $data->password,
            'token' => $data->token,
        ], function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)]
            ]);
        }
    }
}
