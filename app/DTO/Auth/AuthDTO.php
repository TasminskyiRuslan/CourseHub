<?php

namespace App\DTO\Auth;

use App\Models\User;
use DateTimeInterface;

final readonly class AuthDTO
{
    public function __construct(
        public User              $user,
        public string            $token,
        public DateTimeInterface $expiresAt,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'user'       => $this->user,
            'token'      => $this->token,
            'expires_at' => $this->expiresAt,
        ], fn($value) => !is_null($value));
    }
}
