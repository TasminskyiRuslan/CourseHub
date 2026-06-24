<?php

namespace App\Actions\Account;

use App\Data\Account\Requests\UpdateAccountData;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateAccountAction
{
    /**
     * Update the specified user account.
     *
     * @param UpdateAccountData $accountData
     * @param User $account
     * @return User
     */
    public function handle(UpdateAccountData $accountData, User $account): User
    {
        if ($account->hasRole(UserRole::SUPER_ADMIN->value)) {
            throw ValidationException::withMessages([
                'email' => [__('users.protected')],
            ]);
        }

        $account->update($accountData->all());
        return $account;
    }
}
