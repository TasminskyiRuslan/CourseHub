<?php

namespace App\Actions\Account;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeleteAccountAvatarAction
{
    /**
     * Remove the current user account image.
     *
     * @param User $account
     * @return void
     */
    public function handle(User $account): void
    {
        if ($account->hasRole(UserRole::SUPER_ADMIN->value)) {
            throw ValidationException::withMessages([
                'email' => [__('users.protected')],
            ]);
        }

        if ($account->avatar_path) {
            Storage::disk('users')->delete($account->avatar_path);
            $account->removeAvatar()->save();
        }
    }
}
