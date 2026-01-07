<?php

namespace App\Services\Auth;

use App\DTO\Auth\ResetPasswordDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Str;

class ResetPasswordService
{
    public function sendResetLink(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages(['email' => ['No user with this email was found.']]);
        }

        $token = Password::createToken($user);
        $user->sendPasswordResetNotification($token);
    }

    public function reset(ResetPasswordDTO $dto): void
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
