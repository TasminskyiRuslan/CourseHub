<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

readonly class VerifyEmailAction
{
    /**
     * Verify the email address of the user identified by the id.
     *
     * @throws AccessDeniedHttpException
     */
    public function handle(string $id, string $hash): void
    {
        $foundUser = User::query()->find($id);

        if (! $foundUser || ! hash_equals($hash, sha1($foundUser->getEmailForVerification()))) {
            throw new AccessDeniedHttpException(__('auth.invalid_verification_link'));
        }

        if ($foundUser->hasVerifiedEmail()) {
            return;
        }

        $foundUser->markEmailAsVerified();

        event(new Verified($foundUser));
    }
}
