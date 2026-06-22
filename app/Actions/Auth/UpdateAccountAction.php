<?php

namespace App\Actions\Auth;

use App\Data\Auth\Requests\UpdateAccountData;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateAccountAction
{
    /**
     * Update the specified user account.
     *
     * @param UpdateAccountData $accountData
     * @param User $user
     * @return User
     * @throws Throwable
     */
    public function handle(UpdateAccountData $accountData, User $user): User
    {
        return DB::transaction(function () use ($accountData, $user) {
            $user->update($accountData->all());
            return $user;
        });
    }
}
