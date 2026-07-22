<?php

namespace App\Actions\Auth;

use App\Data\Auth\Requests\RegisterUserData;
use App\Data\Auth\Results\AuthResultData;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class RegisterUserAction
{
    /**
     * @param IssueAccessTokenAction $issueAccessTokenAction
     */
    public function __construct(
        protected IssueAccessTokenAction $issueAccessTokenAction,
    ) {}

    /**
     * Register a new user, assign roles, and issue an access token.
     *
     * @param RegisterUserData $data
     * @return AuthResultData
     * @throws Throwable
     */
    public function handle(RegisterUserData $data): AuthResultData
    {
        return DB::transaction(function () use ($data) {
            $user = User::query()->create($data->all());

            $user->syncRoles($data->roles);
            $user->loadMissing(['roles']);

            $accessTokenData = $this->issueAccessTokenAction->handle($user);

            event(new Registered($user));

            return new AuthResultData(
                user: $user,
                accessToken: $accessTokenData->plainTextToken,
                expiresAt: $accessTokenData->accessToken->expires_at,
            );
        });
    }
}
