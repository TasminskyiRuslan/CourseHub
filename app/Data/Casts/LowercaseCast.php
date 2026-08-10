<?php

declare(strict_types=1);

namespace App\Data\Casts;

use Illuminate\Support\Str;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class LowercaseCast implements Cast
{
    /**
     * Cast the given value to lowercase.
     */
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): string
    {
        return Str::lower($value);
    }
}
