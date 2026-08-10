<?php

declare(strict_types=1);

namespace App\Data\User\Requests;

use App\Enums\UserRole;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class UpdateUserRoleData extends Data
{
    /**
     * @param  array<int, string>  $roles
     */
    public function __construct(
        public array $roles,
    ) {}

    /**
     * Return the validation rules.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(ValidationContext $context): array
    {
        $currentUser = request()->user();

        $allowedRoles = [
            UserRole::TEACHER->value,
        ];

        if ($currentUser?->hasRole(UserRole::SUPER_ADMIN->value)) {
            $allowedRoles[] = UserRole::ADMIN->value;
        }

        return [
            'roles' => ['present', 'array'],
            'roles.*' => [
                'string',
                'distinct',
                Rule::enum(UserRole::class),
                Rule::in($allowedRoles),
            ],
        ];
    }
}
