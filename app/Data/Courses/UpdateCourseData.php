<?php

namespace App\Data\Courses;

use App\Data\Casts\SlugCast;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class UpdateCourseData extends Data
{
    public function __construct(
        #[Required]
        #[StringType]
        #[Max(255)]
        public string  $title,

        #[Required]
        #[StringType]
        #[Max(255)]
        #[WithCast(SlugCast::class)]
        public string  $slug,

        #[Nullable]
        #[Present]
        #[StringType]
        public ?string $description,

        #[Required]
        #[Numeric]
        #[Min(0)]
        #[Max(99999999.99)]
        public string  $price,
    )
    {
    }

    public static function rules(): array
    {
        $course = request()->route('course');

        return [
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courses', 'slug')->ignore($course),
            ],
        ];
    }
}
