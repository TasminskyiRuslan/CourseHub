<?php

namespace App\Services\Auth;

use App\DTO\Auth\AuthDTO;
use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\Exceptions\Auth\EmailVerificationFailedException;
use App\Models\User;
use DateTimeInterface;
use DB;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AuthService
{
    /**
     * @throws Throwable
     */
    public function register(RegisterDTO $dto): AuthDTO
    {
        return DB::transaction(function () use ($dto) {
            $user = User::create($dto->toArray());

            event(new Registered($user));

            return $this->issueToken($user, $dto->remember);
        });
    }

    protected function issueToken(User $user, bool $remember): AuthDTO
    {
        $expiresAt = $this->getExpirationDate($remember);
        $token = $this->generateToken($user, $expiresAt);
        return new AuthDTO($user, $token, $expiresAt);
    }

    protected function getExpirationDate(bool $remember): DateTimeInterface
    {
        return $remember ? now()->addWeeks(2) : now()->addDay();
    }

    protected function generateToken(User $user, DateTimeInterface $expiresAt): string
    {
        return $user->createToken('access_token', ['*'], $expiresAt)->plainTextToken;
    }

    /**
     * @throws EmailVerificationFailedException
     */
    public function login(LoginDTO $dto): AuthDTO
    {
        $user = User::where('email', $dto->email)->first();

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password'],
            ]);
        }

        if (!$user->hasVerifiedEmail()) {
            throw new EmailVerificationFailedException('Email address is not verified.', HttpResponse::HTTP_FORBIDDEN);
        }

        return $this->issueToken($user, $dto->remember);
    }

    public function logout(User $user, bool $allDevices = false): void
    {
        if ($allDevices) {
            $user->tokens()->delete();
        } else {
            $user->currentAccessToken()?->delete();
        }
    }
}
