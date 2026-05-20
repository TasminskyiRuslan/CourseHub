<?php

namespace App\Data\User;

use App\Enums\UserRole;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class UpdateUserRoleData extends Data
{
    /**
     * @param UserRole $role
     */
    public function __construct(
        #[Required]
        #[StringType]
        #[Enum(enum: UserRole::class)]
        #[In(UserRole::STUDENT->value, UserRole::TEACHER->value, UserRole::ADMIN->value)]
        public UserRole $role,
    ) {}
}
