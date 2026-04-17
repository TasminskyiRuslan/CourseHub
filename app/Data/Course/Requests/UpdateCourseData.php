<?php

namespace App\Data\Course\Requests;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;

class UpdateCourseData extends Data
{
    /**
     * @param string|Optional $title
     * @param string|Optional $slug
     * @param string|Optional|null $description
     * @param string|Optional $price
     */
    public function __construct(
        #[Sometimes]
        #[StringType]
        #[Max(255)]
        public string|Optional  $title,

        #[Sometimes]
        #[StringType]
        #[Max(255)]
        #[Unique(table: 'courses', column: 'slug', ignore: new RouteParameterReference('course.id'))]
        #[Regex('/^[a-z0-9-]+$/')]
        public string|Optional  $slug,

        #[Sometimes]
        #[Nullable]
        #[StringType]
        #[Max(5000)]
        public string|Optional|null $description,

        #[Sometimes]
        #[Numeric]
        #[Min(0)]
        #[Max(99999999.99)]
        public string|Optional  $price,
    )
    {
    }
}
