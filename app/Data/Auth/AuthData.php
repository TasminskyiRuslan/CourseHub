<?php

namespace App\Data\Auth;

use App\Models\User;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

final class AuthData extends Data
{
    public function __construct(
        public User $user,
        public string $token,
        public Carbon $expiresAt,
    ) {}
}
