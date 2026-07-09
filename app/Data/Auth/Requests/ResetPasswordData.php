<?php

namespace App\Data\Auth\Requests;

use App\Data\Casts\LowercaseCast;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ResetPasswordData extends Data
{
    /**
     * @param string $email
     * @param string $password
     * @param string $token
     */
    public function __construct(
        #[Required]
        #[Email]
        #[Max(255)]
        #[Exists(table: 'users', column: 'email')]
        #[WithCast(castClass: LowercaseCast::class)]
        public string $email,

        #[Required]
        #[StringType]
        #[Confirmed]
        #[Password(min: 8)]
        public string $password,

        #[Required]
        #[StringType]
        public string $token,
    )
    {
    }
}
