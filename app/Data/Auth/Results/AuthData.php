<?php

namespace App\Data\Auth\Results;

use App\Models\User;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

final class AuthData extends Data
{
    /**
     * @param User $user
     * @param string $accessToken
     * @param Carbon $expiresAt
     * @param string|null $tokenType
     */
    public function __construct(
        public User   $user,
        public string $accessToken,
        public Carbon $expiresAt,
        public ?string $tokenType = 'Bearer',
    )
    {
    }
}
