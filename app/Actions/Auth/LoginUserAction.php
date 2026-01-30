<?php

namespace App\Actions\Auth;

use App\Data\Auth\AuthData;
use App\Data\Auth\LoginData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginUserAction
{
    public function __construct(
        protected IssueTokenAction $issueTokenAction,
    )
    {
    }

    public function handle(LoginData $data): AuthData
    {
        $user = User::where('email', $data->email)->first();

        if (!$user || !Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password'],
            ]);
        }

        return $this->issueTokenAction->handle($user, $data->remember);
    }
}
