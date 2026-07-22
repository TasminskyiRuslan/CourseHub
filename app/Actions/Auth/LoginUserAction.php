<?php

namespace App\Actions\Auth;

use App\Data\Auth\Requests\LoginUserData;
use App\Data\Auth\Results\AuthResultData;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

readonly class LoginUserAction
{
    /**
     * @param IssueAccessTokenAction $issueAccessTokenAction
     */
    public function __construct(
        protected IssueAccessTokenAction $issueAccessTokenAction,
    ) {}

    /**
     * Authenticate a user and issue a new access token.
     *
     * @param LoginUserData $data
     * @return AuthResultData
     * @throws ValidationException
     */
    public function handle(LoginUserData $data): AuthResultData
    {
        $user = User::where('email', $data->email)->first();

        if (!$user || !Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $user->loadMissing(['roles']);

        $accessTokenData = $this->issueAccessTokenAction->handle($user, $data->remember);

        event(new Login(config('auth.defaults.guard'), $user, $data->remember));

        return new AuthResultData(
            user: $user,
            accessToken: $accessTokenData->plainTextToken,
            expiresAt: $accessTokenData->accessToken->expires_at,
        );
    }
}
