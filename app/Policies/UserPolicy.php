<?php

namespace App\Policies;

use App\Enums\UserPermission;
use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view the list of users.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can(UserPermission::USER_VIEW_ANY->value);
    }

    /**
     * Determine whether the user can view the specific user's details.
     *
     * @param User $user
     * @param User $targetUser
     * @return bool
     */
    public function view(User $user, User $targetUser): bool
    {
        return $user->can(UserPermission::USER_VIEW_ANY->value);
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
        return $user->can(UserPermission::USER_DELETE_ANY->value) && !$user->is($targetUser) && !$targetUser->hasAnyRole([UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value]);
    }

    /**
     * Determine whether the user can update the target user's role.
     *
     * @param User $user
     * @param User $targetUser
     * @return bool
     */
    public function updateRole(User $user, User $targetUser): bool
    {
        return $user->can(UserPermission::USER_ROLE_EDIT_ANY->value) && !$user->is($targetUser) && !$targetUser->hasAnyRole([UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value]);
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
        return $user->can(UserPermission::USER_BAN_ANY->value) && !$user->is($targetUser) && !$targetUser->hasAnyRole([UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value]);
    }

    /**
     * Determine whether the user can unban the target user.
     *
     * @param User $user
     * @param User $targetUser
     * @return bool
     */
    public function unban(User $user, User $targetUser): bool
    {
        return $user->can(UserPermission::USER_UNBAN_ANY->value) && !$user->is($targetUser) && !$targetUser->hasAnyRole([UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value]);
    }
}
