<?php

declare(strict_types=1);

namespace App\Data\Auth\Results;

use App\Models\User;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class AuthResultData extends Data
{
    public function __construct(
        public User $user,
        public string $accessToken,
        public ?Carbon $expiresAt = null,
        public ?string $tokenType = 'Bearer',
    ) {}
}
