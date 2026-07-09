<?php

namespace App\Policies;

use App\Enums\UserPermission;
use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can update the target user roles.
     *
     * @param User $user
     * @param User $targetUser
     * @return bool
     */
    public function updateRoles(User $user, User $targetUser): bool
    {
        if ($targetUser->hasAnyRole([UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value])) {
            return false;
        }

        if ($user->is($targetUser)) {
            return false;
        }

        return $user->can(UserPermission::USERS_UPDATE_ROLES_ALL->value);
    }

    /**
     * Determine whether the user can delete the target user.
     *
     * @param User $user
     * @param User $targetUser
     * @return bool
     */
    public function delete(User $user, User $targetUser): bool
    {
        if ($targetUser->hasAnyRole([UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value])) {
            return false;
        }

        if ($user->is($targetUser)) {
            return false;
        }

        return $user->can(UserPermission::USERS_DELETE_ALL->value);
    }

    /**
     * Determine whether the user can ban the target user.
     *
     * @param User $user
     * @param User $targetUser
     * @return bool
     */
    public function ban(User $user, User $targetUser): bool
    {
        if ($targetUser->hasAnyRole([UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value])) {
            return false;
        }

        if ($user->is($targetUser)) {
            return false;
        }

        return $user->can(UserPermission::USERS_BAN_ALL->value);
    }
}
