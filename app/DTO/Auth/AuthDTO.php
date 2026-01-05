<?php

namespace App\DTO\Auth;

use App\Models\User;
use DateTimeInterface;

readonly class AuthDTO
{
    public function __construct(
        public User              $user,
        public string            $token,
        public DateTimeInterface $expiresAt,
    ) {}
}
