<?php

namespace App\Services;

use App\DTO\Auth\RegisterDTO;
use App\DTO\Auth\LoginDTO;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AuthService
{
    public function registerUser(RegisterDTO $dto): array
    {
        $user = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
        ]);

        event(new Registered($user));

        $authTokenData = $this->generateAuthToken($user, $dto->remember);

        return [
            'user' => $user,
            'auth_token' => $authTokenData['auth_token'],
            'expires_at' => $authTokenData['expires_at']
        ];
    }

    public function loginUser(LoginDTO $dto): array
    {
        $user = User::where('email', $dto->email)->first();

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password'],
            ]);
        }

        if (!$user->hasVerifiedEmail()) {
            throw new AccessDeniedHttpException('Email address is not verified.');
        }

        $authTokenData = $this->generateAuthToken($user, $dto->remember);

        return [
            'user' => $user,
            'auth_token' => $authTokenData['auth_token'],
            'expires_at' => $authTokenData['expires_at']
        ];
    }

    public function logoutUser(User $user, bool $allDevices = false): void
    {
        if ($allDevices) {
            $user->tokens()->delete();
        } else {
            $user->currentAccessToken()?->delete();
        }
    }

    private function generateAuthToken(User $user, bool $remember): array
    {
        $expiresAt = $remember ? now()->addWeeks(2) : now()->addDay();
        $authToken = $user->createToken('auth_token', ['*'], $expiresAt)->plainTextToken;

        return [
            'auth_token' => $authToken,
            'expires_at' => $expiresAt
        ];
    }
}
