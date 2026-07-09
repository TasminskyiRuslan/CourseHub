<?php

namespace App\Queries\User;

use App\Enums\UserPermission;
use App\Models\User;

class LoadUserRelationsQuery
{
    /**
     * Load user relations and counts dynamically based on permissions.
     *
     * @param User $user
     * @return User
     */
    public function handle(User $user): User
    {
        return $user->loadMissing(['roles'])->loadCount(['courses']);
    }
}
