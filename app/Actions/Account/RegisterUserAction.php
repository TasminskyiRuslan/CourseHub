<?php

namespace App\Actions\Account;

use App\Data\Account\Requests\RegisterUserData;
use App\Data\Account\Results\AccountData;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Throwable;

class RegisterUserAction
{
    /**
     * @param IssueAccessTokenAction $issueAccessTokenAction
     */
    public function __construct(
        protected IssueAccessTokenAction $issueAccessTokenAction,
    )
    {
    }

    /**
     * Create a new user account and issue an access token.
     *
     * @param RegisterUserData $userData
     * @return AccountData
     * @throws Throwable
     */
    public function handle(RegisterUserData $userData): AccountData
    {
        return DB::transaction(function () use ($userData) {
            $user = User::create($userData->all());
            $user->assignRole($userData->role)->load('roles');
            $accessTokenData = $this->issueAccessTokenAction->handle($user);

            event(new Registered($user));

            return new AccountData(
                user: $user,
                accessToken: $accessTokenData->plainTextToken,
                expiresAt: $accessTokenData->accessToken->expires_at,
            );
        });
    }
}
