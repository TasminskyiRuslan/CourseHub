<?php

namespace App\Actions\Account;

use App\Data\Account\Requests\UpdateAccountImageData;
use App\Enums\UserRole;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UpdateAccountAvatarAction
{
    /**
     * Update the specified account image.
     *
     * @param UpdateAccountImageData $accountImageData
     * @param User $account
     * @return User
     * @throws Exception
     */
    public function handle(UpdateAccountImageData $accountImageData, User $account): User
    {
        if ($account->hasRole(UserRole::SUPER_ADMIN->value)) {
            throw ValidationException::withMessages([
                'email' => [__('users.protected')],
            ]);
        }

        $oldPath = $account->avatar_path;
        $newPath = $accountImageData->image->store('/', 'users');

        try {
            $account->setAvatar($newPath)->save();
        } catch (Exception $e) {
            Storage::disk('users')->delete($newPath);
            throw $e;
        }

        if ($oldPath && Storage::disk('users')->exists($oldPath)) {
            Storage::disk('users')->delete($oldPath);
        }

        return $account;
    }
}
