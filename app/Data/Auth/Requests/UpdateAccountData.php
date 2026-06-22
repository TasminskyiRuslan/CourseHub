<?php

namespace App\Data\Auth\Requests;

use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdateAccountData extends Data
{
    public function __construct(
        #[Sometimes]
        #[StringType]
        #[Min(2)]
        #[Max(100)]
        public string|Optional   $name,

        public string|Optional      $slug,
    ) {}

    public static function rules(): array
    {
        return [
            'slug' => [
                'sometimes',
                'string',
                'max:100',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('users', 'slug')->ignore(auth()->id()),
            ],
        ];
    }
}
