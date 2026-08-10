<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserPermission;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can update the target user roles.
     */
    public function updateRoles(User $user, User $targetUser): Response
    {
        if ($targetUser->hasAnyRole([UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value])) {
            return Response::deny(__('users.forbidden.protected'));
        }

        if ($user->is($targetUser)) {
            return Response::deny(__('users.forbidden.update_roles_self'));
        }

        if (! $user->can(UserPermission::USERS_UPDATE_ROLES_ALL->value)) {
            return Response::deny(__('users.forbidden.update_roles'));
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete the target user.
     */
    public function delete(User $user, User $targetUser): Response
    {
        if ($targetUser->hasAnyRole([UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value])) {
            return Response::deny(__('users.forbidden.protected'));
        }

        if ($user->is($targetUser)) {
            return Response::deny(__('users.forbidden.delete_self'));
        }

        if (! $user->can(UserPermission::USERS_DELETE_ALL->value)) {
            return Response::deny(__('users.forbidden.delete'));
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can ban the target user.
     */
    public function ban(User $user, User $targetUser): Response
    {
        if ($targetUser->hasAnyRole([UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value])) {
            return Response::deny(__('users.forbidden.protected'));
        }

        if ($user->is($targetUser)) {
            return Response::deny(__('users.forbidden.ban_self'));
        }

        if (! $user->can(UserPermission::USERS_BAN_ALL->value)) {
            return Response::deny(__('users.forbidden.ban'));
        }

        return Response::allow();
    }
}
