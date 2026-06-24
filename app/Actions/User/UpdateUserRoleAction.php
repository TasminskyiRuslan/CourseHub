<?php

namespace App\Actions\User;

use App\Data\User\UpdateUserRoleData;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class UpdateUserRoleAction
{
    /**
     * Update the role for the specified user.
     *
     * @param UpdateUserRoleData $userRoleData
     * @param User $user
     * @return User
     * @throws Throwable
     */
    public function handle(UpdateUserRoleData $userRoleData, User $user): User
    {
        if ($user->hasRole(UserRole::SUPER_ADMIN->value)) {
            throw ValidationException::withMessages([
                'email' => [__('users.protected')],
            ]);
        }

        return DB::transaction(function () use ($userRoleData, $user) {
            $user->syncRoles([$userRoleData->role->value]);
            return $user;
        });
    }
}
