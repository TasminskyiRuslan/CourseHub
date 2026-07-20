<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyEmailAction
{
    /**
     * Verify the email address of the user identified by the id.
     *
     * @param string $id
     * @param string $hash
     * @return void
     * @throws AccessDeniedHttpException
     */
    public function handle(string $id, string $hash): void
    {
        $user = User::query()->find($id);

        if (!$user || !hash_equals($hash, sha1($user->getEmailForVerification()))) {
            throw new AccessDeniedHttpException(__('auth.invalid_verification_link'));
        }

        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->markEmailAsVerified();

        event(new Verified($user));
    }
}
