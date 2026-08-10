<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Data\Auth\Requests\LoginUserData;
use App\Data\Auth\Results\AuthResultData;
use App\Loaders\User\Account\UserLoader;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

readonly class LoginUserAction
{
    public function __construct(
        protected IssueAccessTokenAction $issueAccessTokenAction,
        protected UserLoader $userLoader,
    ) {}

    /**
     * Authenticate a user and issue a new access token.
     *
     * @throws ValidationException
     */
    public function handle(LoginUserData $data): AuthResultData
    {
        $gottenUser = User::query()->where('email', $data->email)->first();

        if (! $gottenUser || ! Hash::check($data->password, $gottenUser->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $loadedUser = $this->userLoader->handle($gottenUser);

        $accessTokenData = $this->issueAccessTokenAction->handle($loadedUser, $data->remember);

        event(new Login(config('auth.defaults.guard'), $loadedUser, $data->remember));

        return new AuthResultData(
            user: $loadedUser,
            accessToken: $accessTokenData->plainTextToken,
            expiresAt: $accessTokenData->accessToken->expires_at,
        );
    }
}
