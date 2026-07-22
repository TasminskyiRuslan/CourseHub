<?php

namespace App\Actions\User;

use App\Data\User\Requests\UpdateUserRoleData;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

readonly class UpdateUserRoleAction
{
    /**
     * Update the role for the specified user.
     *
     * @param UpdateUserRoleData $data
     * @param User $user
     * @return User
     * @throws AccessDeniedHttpException
     * @throws Throwable
     */
    public function handle(UpdateUserRoleData $data, User $user): User
    {
        if ($user->hasRole(UserRole::SUPER_ADMIN->value)) {
            throw new AccessDeniedHttpException(__('users.protected'));
        }

        return DB::transaction(function () use ($data, $user) {
            $user->syncRoles($data->roles);

            return $user;
        });
    }
}
