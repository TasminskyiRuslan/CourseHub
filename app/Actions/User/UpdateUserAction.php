<?php

namespace App\Actions\User;

use App\Data\User\Requests\UpdateUserData;
use App\Enums\UserRole;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UpdateUserAction
{
    /**
     * Update the specified user.
     *
     * @param UpdateUserData $data
     * @param User $user
     * @return User
     * @throws AccessDeniedHttpException
     */
    public function handle(UpdateUserData $data, User $user): User
    {
        if ($user->hasRole(UserRole::SUPER_ADMIN->value)) {
            throw new AccessDeniedHttpException(__('users.protected'));

        }

        $user->update($data->all());

        return $user;
    }
}
