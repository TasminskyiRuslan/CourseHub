<?php

namespace App\Queries\Concerns;

use App\Enums\UserPermission;
use App\Enums\UserRole;
use App\Models\User;

trait HasCacheBypass
{
    /**
     * Determine whether the cache should be bypassed for the given user.
     *
     * @param User|null $user
     * @return bool
     */
    protected function shouldBypassCache(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->hasAnyPermission(
                UserPermission::COURSE_VIEW_UNPUBLISHED->value,
                UserPermission::COURSE_CREATE->value,
                UserPermission::LESSON_CREATE->value,
            ) || $user->hasRole(UserRole::SUPER_ADMIN->value);
    }
}
