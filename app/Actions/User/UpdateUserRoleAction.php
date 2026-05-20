<?php

namespace App\Actions\User;

use App\Data\User\UpdateUserRoleData;
use App\Models\User;

class UpdateUserRoleAction
{
    /**
     * Update the role for the specified user.
     *
     * @param UpdateUserRoleData $userRoleData
     * @param User $user
     * @return User
     */
    public function handle(UpdateUserRoleData $userRoleData, User $user): User
    {
        $user->syncRoles([$userRoleData->role->value]);
        return $user;
    }
}
