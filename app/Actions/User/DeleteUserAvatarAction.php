<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Enums\UserRole;
use App\Jobs\DeleteFileFromStorageJob;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

readonly class DeleteUserAvatarAction
{
    /**
     * Delete the specified user avatar.
     *
     * @throws AccessDeniedHttpException
     */
    public function handle(User $user): void
    {
        if ($user->hasRole(UserRole::SUPER_ADMIN->value)) {
            throw new AccessDeniedHttpException(__('users.protected'));
        }

        $avatarPath = $user->avatar_path;

        if (! $avatarPath) {
            return;
        }

        $user->removeAvatar()->save();

        DeleteFileFromStorageJob::dispatch('users', $avatarPath);
    }
}
