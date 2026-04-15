<?php

namespace App\Data\Course\Requests;

use App\Enums\CourseType;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;

class CreateCourseData extends Data
{
    /**
     * @param string $title
     * @param string|null $slug
     * @param string|null $description
     * @param CourseType $type
     * @param string $price
     */
    public function __construct(
        #[Required]
        #[StringType]
        #[Max(255)]
        public string     $title,

        #[Nullable]
        #[StringType]
        #[Max(255)]
        #[Unique(table: 'courses', column: 'slug')]
        #[Regex('/^[a-z0-9-]+$/')]
        public ?string    $slug,

        #[Nullable]
        #[StringType]
        #[Max(5000)]
        public ?string    $description,

        #[Required]
        #[StringType]
        #[Enum(CourseType::class)]
        public CourseType $type,

        #[Required]
        #[Numeric]
        #[Min(0)]
        #[Max(99999999.99)]
        public string     $price,
    )
    {
    }
}
