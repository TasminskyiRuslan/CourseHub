<?php

declare(strict_types=1);

namespace App\Data\Course\Requests;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;

class UpdateCourseData extends Data
{
    public function __construct(
        #[Sometimes]
        #[StringType]
        #[Max(255)]
        public string|Optional $title,

        #[Sometimes]
        #[StringType]
        #[Max(255)]
        #[Unique(table: 'courses', column: 'slug', ignore: new RouteParameterReference('teacherCourse.id'))]
        #[Regex('/^[a-z0-9-]+$/')]
        public string|Optional $slug,

        #[Sometimes]
        #[Nullable]
        #[StringType]
        #[Max(5000)]
        public string|Optional|null $description,

        #[Sometimes]
        #[Regex('/^\d{1,8}(\.\d{1,2})?$/')]
        public string|Optional $price,
    ) {}
}
