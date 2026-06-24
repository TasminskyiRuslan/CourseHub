<?php

namespace App\Actions\User;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DeleteUserAction
{
    /**
     * Remove the specified user.
     *
     * @param User $user
     * @return void
     */
    public function handle(User $user): void
    {
        if ($user->hasRole(UserRole::SUPER_ADMIN->value)) {
            throw ValidationException::withMessages([
                'email' => [__('users.protected')],
            ]);
        }

        $user->delete();
    }
}
