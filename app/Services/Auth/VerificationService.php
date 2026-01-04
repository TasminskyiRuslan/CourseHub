<?php

namespace App\Services\Auth;

use App\Exceptions\Auth\EmailVerificationFailedException;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

class VerificationService {
    /**
     * @throws EmailVerificationFailedException
     */
    public function verify(int $id, string $hash): User {
        $user = User::findOrFail($id);

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            throw new EmailVerificationFailedException('Invalid verification link.');
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return $user;
    }

    public function resendVerificationEmail(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $user->sendEmailVerificationNotification();

        return true;
    }
}
