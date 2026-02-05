<?php

namespace App\Data\Auth;

use App\Data\Casts\LowercaseCast;
use App\Enums\UserRole;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class RegisterData extends Data
{
    public function __construct(
        #[Required]
        #[StringType]
        #[Max(255)]
        public string   $name,

        #[Required]
        #[Email]
        #[Max(255)]
        #[WithCast(LowercaseCast::class)]
        #[Unique('users', 'email')]
        public string   $email,

        #[Required]
        #[StringType]
        #[Confirmed]
        #[Password(min: 8)]
        public string   $password,

        #[Required]
        #[Enum(UserRole::class)]
        #[In([UserRole::STUDENT->value, UserRole::TEACHER->value])]
        public UserRole $role,

        #[BooleanType]
        public bool     $remember = false,
    )
    {
    }
}
