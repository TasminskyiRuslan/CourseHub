<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Enums\UserRole;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

readonly class DeleteUserAction
{
    /**
     * Delete the specified user.
     *
     * @throws AccessDeniedHttpException
     */
    public function handle(User $user): void
    {
        if ($user->hasRole(UserRole::SUPER_ADMIN->value)) {
            throw new AccessDeniedHttpException(__('users.protected'));
        }

        $user->delete();
    }
}
