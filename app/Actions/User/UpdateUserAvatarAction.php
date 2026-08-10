<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Data\User\Requests\UpdateUserAvatarData;
use App\Enums\UserRole;
use App\Jobs\DeleteFileFromStorageJob;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

readonly class UpdateUserAvatarAction
{
    /**
     * Update the specified user avatar.
     *
     * @throws AccessDeniedHttpException
     * @throws Throwable
     */
    public function handle(UpdateUserAvatarData $data, User $user): User
    {
        if ($user->hasRole(UserRole::SUPER_ADMIN->value)) {
            throw new AccessDeniedHttpException(__('users.protected'));
        }

        $oldAvatarPath = $user->avatar_path;
        $newAvatarPath = $data->avatar->store('/', 'users');

        try {
            $user->setAvatar($newAvatarPath)->save();

            if ($oldAvatarPath) {
                DeleteFileFromStorageJob::dispatch('users', $oldAvatarPath);
            }
        } catch (Throwable $e) {
            Storage::disk('users')->delete($newAvatarPath);

            throw $e;
        }

        return $user;
    }
}
