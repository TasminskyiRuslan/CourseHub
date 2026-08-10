<?php

declare(strict_types=1);

namespace App\Data\User\Requests;

use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class UpdateUserData extends Data
{
    public function __construct(
        #[Sometimes]
        #[StringType]
        #[Min(2)]
        #[Max(100)]
        public string|Optional $name,

        public string|Optional $slug,
    ) {}

    /**
     * Return the validation rules.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(ValidationContext $context): array
    {
        $user = Route::current()?->parameter('user') ?? request()->user();

        return [
            'slug' => [
                'sometimes',
                'string',
                'max:100',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('users', 'slug')->ignore($user),
            ],
        ];
    }
}
