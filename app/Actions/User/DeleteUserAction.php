<?php

namespace App\Actions\User;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DeleteUserAction
{
    /**
     * Delete the specified user.
     *
     * @param User $user
     * @return void
     * @throws AccessDeniedHttpException
     */
    public function handle(User $user): void
    {
        if ($user->hasRole(UserRole::SUPER_ADMIN->value)) {
            throw new AccessDeniedHttpException(__('users.protected'));
        }

        if ($user->avatar_path && Storage::disk('users')->exists($user->avatar_path)) {
            Storage::disk('users')->delete($user->avatar_path);
        }

        $user->delete();
    }
}
