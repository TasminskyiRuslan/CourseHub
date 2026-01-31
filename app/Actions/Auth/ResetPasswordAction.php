<?php

namespace App\Actions\Auth;

use App\Data\Auth\ResetPasswordData;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Str;

class ResetPasswordAction
{
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
            ])->setRememberToken(Str::random(60));
            $user->save();
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }
    }
}
