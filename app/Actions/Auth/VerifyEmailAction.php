<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyEmailAction
{
    /**
     * Verify the user's email address.
     *
     * @param string $id
     * @param string $hash
     * @return void
     */
    public function handle(string $id, string $hash): void
    {
        $user = User::find($id);

        if (!$user || !hash_equals($hash, sha1($user->getEmailForVerification()))) {
            throw new AccessDeniedHttpException();
        }

        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->markEmailAsVerified();
        event(new Verified($user));
    }
}
