<?php

namespace App\Data\Auth\Requests;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;

class UpdateAccountData extends Data
{
    public function __construct(
        #[Sometimes]
        #[StringType]
        #[Min(2)]
        #[Max(100)]
        public string|Optional   $name,

        #[Sometimes]
        #[StringType]
        #[Max(100)]
        #[Unique(table: 'users', column: 'slug', ignore: new RouteParameterReference('user.id'))]
        #[Regex('/^[a-z0-9-]+$/')]
        public string|Optional      $slug,
    ) {}
}
