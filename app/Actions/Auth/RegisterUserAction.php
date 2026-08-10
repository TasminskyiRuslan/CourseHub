<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Data\Auth\Requests\RegisterUserData;
use App\Data\Auth\Results\AuthResultData;
use App\Loaders\User\Account\UserLoader;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class RegisterUserAction
{
    public function __construct(
        protected IssueAccessTokenAction $issueAccessTokenAction,
        protected UserLoader $userLoader,
    ) {}

    /**
     * Register a new user, assign roles, and issue an access token.
     *
     * @throws Throwable
     */
    public function handle(RegisterUserData $data): AuthResultData
    {
        return DB::transaction(function () use ($data): AuthResultData {
            $createdUser = User::query()->create($data->except('roles')->toArray());
            $createdUser->syncRoles($data->roles);
            $loadedUser = $this->userLoader->handle($createdUser);

            $accessTokenData = $this->issueAccessTokenAction->handle($loadedUser);

            event(new Registered($loadedUser));

            return new AuthResultData(
                user: $loadedUser,
                accessToken: $accessTokenData->plainTextToken,
                expiresAt: $accessTokenData->accessToken->expires_at,
            );
        });
    }
}
