<?php

namespace App\Actions\Auth;

use App\Data\Auth\Requests\LoginUserData;
use App\Data\Auth\Results\AuthData;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginUserAction
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
     * Authenticate the user and issue an access token.
     *
     * @param LoginUserData $userData
     * @return AuthData
     * @throws ValidationException
     */
    public function handle(LoginUserData $userData): AuthData
    {
        $user = User::whereEmail($userData->email)->first();
        if (!$user || !Hash::check($userData->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $accessTokenData = $this->issueAccessTokenAction->handle($user, $userData->remember);
        event(new Login(config('auth.defaults.guard'), $user, $userData->remember));
        return new AuthData(
            user: $user,
            accessToken: $accessTokenData->plainTextToken,
            expiresAt: $accessTokenData->accessToken->expires_at,
        );
    }
}
