<?php

namespace App\Actions\Auth;

use App\DTO\Auth\AuthDTO;
use App\DTO\Auth\LoginDTO;
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

    public function handle(LoginDTO $dto): AuthDTO
    {
        $user = User::where('email', $dto->email)->first();

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password'],
            ]);
        }

        $remember = $dto->remember ?? false;

        return $this->issueTokenAction->handle($user, $remember);
    }
}
