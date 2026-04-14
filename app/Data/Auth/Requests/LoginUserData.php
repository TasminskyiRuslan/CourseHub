<?php

namespace App\Data\Auth\Requests;

use App\Data\Casts\LowercaseCast;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class LoginUserData extends Data
{
    /**
     * @param string $email
     * @param string $password
     * @param bool $remember
     */
    public function __construct(
        #[Required]
        #[Email]
        #[Max(255)]
        #[WithCast(castClass: LowercaseCast::class)]
        public string $email,

        #[Required]
        #[StringType]
        public string $password,

        #[Sometimes]
        #[BooleanType]
        public bool   $remember = false,
    )
    {
    }
}
