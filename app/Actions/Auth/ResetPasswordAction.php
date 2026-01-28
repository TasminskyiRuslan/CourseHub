<?php

namespace App\Actions\Auth;

use App\DTO\Auth\ResetPasswordDTO;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Str;

class ResetPasswordAction
{
    public function handle(ResetPasswordDTO $dto): void
    {
        $status = Password::reset([
            'email' => $dto->email,
            'password' => $dto->password,
            'password_confirmation' => $dto->password,
            'token' => $dto->token,
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
