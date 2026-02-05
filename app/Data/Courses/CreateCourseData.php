<?php

namespace App\Data\Courses;

use App\Data\Casts\SlugCast;
use App\Enums\CourseType;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class CreateCourseData extends Data
{
    public function __construct(
        #[Required]
        #[StringType]
        #[Max(255)]
        public string     $title,

        #[Nullable]
        #[StringType]
        #[Max(255)]
        #[WithCast(SlugCast::class)]
        #[Unique('courses', 'slug')]
        public ?string    $slug,

        #[Nullable]
        #[StringType]
        public ?string    $description,

        #[Required]
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
