<?php

namespace App\Data\Auth\Requests;

use App\Data\Casts\LowercaseCast;
use App\Enums\UserRole;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class RegisterUserData extends Data
{
    /**
     * @param string $name
     * @param string $email
     * @param string $password
     * @param UserRole $role
     */
    public function __construct(
        #[Required]
        #[StringType]
        #[Min(2)]
        #[Max(100)]
        public string   $name,

        #[Required]
        #[Email]
        #[Max(255)]
        #[Unique(table: 'users', column: 'email')]
        #[WithCast(castClass: LowercaseCast::class)]
        public string   $email,

        #[Required]
        #[StringType]
        #[Confirmed]
        #[Password(min: 8)]
        public string   $password,

        #[Required]
        #[Enum(enum: UserRole::class, only: [UserRole::STUDENT, UserRole::TEACHER])]
        public UserRole $role,
    )
    {
    }
}
