<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Data\User\Requests\UpdateUserData;
use App\Enums\UserRole;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

readonly class UpdateUserAction
{
    /**
     * Update the specified user.
     *
     * @throws AccessDeniedHttpException
     */
    public function handle(UpdateUserData $data, User $user): User
    {
        if ($user->hasRole(UserRole::SUPER_ADMIN->value)) {
            throw new AccessDeniedHttpException(__('users.protected'));
        }

        $user->update($data->toArray());

        return $user;
    }
}
