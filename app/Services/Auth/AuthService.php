<?php

namespace App\Services\Auth;

use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\Exceptions\Auth\EmailVerificationFailedException;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(RegisterDTO $dto): array
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
        ];
    }

    /**
     * @throws EmailVerificationFailedException
     */
    public function login(LoginDTO $dto): array
    {
        $user = User::where('email', $dto->email)->first();

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password'],
            ]);
        }

        if (!$user->hasVerifiedEmail()) {
            throw new EmailVerificationFailedException('Email address is not verified.');
        }

        $authTokenData = $this->generateAuthToken($user, $dto->remember);

        return [
            'user' => $user,
            'auth_token' => $authTokenData['auth_token'],
        ];
    }

    public function logout(User $user, bool $allDevices = false): void
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
        ];
    }
}
