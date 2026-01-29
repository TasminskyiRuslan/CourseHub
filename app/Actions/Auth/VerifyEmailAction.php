<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;

class VerifyEmailAction
{
    public function handle(string $id, string $hash): void
    {
        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->markEmailAsVerified();
        event(new Verified($user));
    }
}
