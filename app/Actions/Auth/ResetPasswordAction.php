<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Data\Auth\Requests\ResetPasswordData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

readonly class ResetPasswordAction
{
    /**
     * Reset the password for the user identified by the email.
     *
     * @throws ValidationException
     */
    public function handle(ResetPasswordData $data): void
    {
        $resetStatus = Password::reset([
            'email' => $data->email,
            'password' => $data->password,
            'password_confirmation' => $data->password,
            'token' => $data->token,
        ], function (User $user, string $password): void {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();
        });

        if ($resetStatus !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($resetStatus)],
            ]);
        }
    }
}
