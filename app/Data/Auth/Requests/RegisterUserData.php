<?php

namespace App\Data\Auth\Requests;

use App\Data\Casts\LowercaseCast;
use App\Enums\UserRole;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class RegisterUserData extends Data
{
    /**
     * @param string $name
     * @param string $email
     * @param string $password
     * @param array $roles
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

        public array $roles = [],
    )
    {
    }

    /**
     * Return the validation rules.
     *
     * @param ValidationContext $context
     * @return array
     */
    public static function rules(ValidationContext $context): array
    {
        return [
            'roles'   => ['sometimes', 'array'],
            'roles.*' => [
                'string',
                'distinct',
                Rule::enum(UserRole::class),
                Rule::in([
                    UserRole::TEACHER->value,
                ]),
            ],
        ];
    }
}
