<?php

namespace App\Actions\Auth;

use App\Data\Auth\Requests\UpdateAccountImageData;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Storage;

class UpdateAccountImageAction
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
