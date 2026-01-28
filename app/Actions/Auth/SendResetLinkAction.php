<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class SendResetLinkAction
{
    public function handle(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages(['email' => ['No user with this email was found.']]);
        }

        $token = Password::createToken($user);
        $user->sendPasswordResetNotification($token);
    }
}
