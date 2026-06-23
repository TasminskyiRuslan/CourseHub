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
     * @param User $account
     * @return User
     */
    public function handle(UpdateAccountData $accountData, User $account): User
    {
        $account->update($accountData->all());
        return $account;
    }
}
