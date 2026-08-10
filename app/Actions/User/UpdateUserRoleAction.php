<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Data\User\Requests\UpdateUserRoleData;
use App\Enums\UserRole;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

readonly class UpdateUserRoleAction
{
    /**
     * Update the role for the specified user.
     *
     * @throws AccessDeniedHttpException
     */
    public function handle(UpdateUserRoleData $data, User $user): User
    {
        if ($user->hasRole(UserRole::SUPER_ADMIN->value)) {
            throw new AccessDeniedHttpException(__('users.protected'));
        }

        $user->syncRoles($data->roles);

        return $user;
    }
}
