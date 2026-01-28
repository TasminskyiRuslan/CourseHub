<?php

namespace App\Actions\Auth;

use App\Exceptions\Api\Auth\EmailVerificationFailedException;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class VerifyEmailAction
{
    /**
     * @throws EmailVerificationFailedException
     */
    public function handle(string $id, string $hash): void
    {
        $user = User::find($id);

        if (!$user || !hash_equals($hash, sha1($user->getEmailForVerification()))) {
            throw new EmailVerificationFailedException('Invalid or expired verification link.', SymfonyResponse::HTTP_FORBIDDEN);
        }

        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->markEmailAsVerified();
        event(new Verified($user));
    }
}
