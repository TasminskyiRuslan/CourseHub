<?php

namespace App\Actions\User;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DeleteUserAction
{
    /**
     * Remove the specified user.
     *
     * @param User $user
     * @return void
     */
    public function handle(User $user): void
    {
        if ($user->hasRole(UserRole::SUPER_ADMIN->value)) {
            throw new AccessDeniedHttpException(__('users.protected'));
        }

        $avatarPath = $user->avatar_path;
        $user->delete();

        if ($avatarPath && Storage::disk('users')->exists($avatarPath)) {
            Storage::disk('users')->delete($avatarPath);
        }
    }
}
