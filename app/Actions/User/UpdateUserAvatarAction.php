<?php

namespace App\Actions\User;

use App\Data\User\Requests\UpdateUserAvatarData;
use App\Enums\UserRole;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UpdateUserAvatarAction
{
    /**
     * Update the specified user auth avatar.
     *
     * @param UpdateUserAvatarData $data
     * @param User $user
     * @return User
     * @throws Exception
     */
    public function handle(UpdateUserAvatarData $data, User $user): User
    {
        if ($user->hasRole(UserRole::SUPER_ADMIN->value)) {
            throw new AccessDeniedHttpException(__('users.protected'));
        }

        $oldPath = $user->avatar_path;
        $newPath = $data->avatar->store('/', 'users');

        try {
            $user->setAvatar($newPath)->save();
        } catch (Exception $e) {
            Storage::disk('users')->delete($newPath);
            throw $e;
        }

        if ($oldPath && Storage::disk('users')->exists($oldPath)) {
            Storage::disk('users')->delete($oldPath);
        }

        return $user;
    }
}
