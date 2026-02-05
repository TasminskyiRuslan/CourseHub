<?php

namespace App\Data\Lessons;

use App\Data\Casts\SlugCast;
use App\Enums\CourseType;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class CreateLessonData extends Data
{
    public function __construct(
        #[Required]
        #[StringType]
        #[Max(255)]
        public string           $title,

        #[Nullable]
        #[StringType]
        #[Max(255)]
        #[WithCast(SlugCast::class)]
        public ?string          $slug,

        #[Nullable]
        #[IntegerType]
        #[Min(0)]
        public ?int             $position,

        public ?CarbonImmutable $start_time,

        public ?CarbonImmutable $end_time,

        public ?string          $address,

        public ?string          $room_number,

        public ?string          $meeting_link,

        public ?string          $video_url,

        public ?string          $provider,
    )
    {
    }

    public static function rules(?ValidationContext $context = null): array
    {
        $course = request()->route('course');
        $type = $course->type;

        $rules = [
            'slug' => [
                Rule::unique('lessons', 'slug')->where('course_id', $course->id),
            ],
        ];

        $typeRules = match ($type) {
            CourseType::OFFLINE => [
                'start_time' => ['nullable', 'date', 'after_or_equal:today'],
                'end_time' => ['nullable', 'date', 'after:start_time'],
                'address' => ['nullable', 'string', 'max:255'],
                'room_number' => ['nullable', 'string', 'max:50'],
            ],
            CourseType::ONLINE => [
                'start_time' => ['nullable', 'date', 'after_or_equal:today'],
                'end_time' => ['nullable', 'date', 'after:start_time'],
                'meeting_link' => ['nullable', 'url', 'max:2048'],
            ],
            CourseType::VIDEO => [
                'video_url' => ['nullable', 'url', 'max:2048'],
                'provider' => ['nullable', 'string', 'max:50'],
            ],
        };
        return array_merge($rules, $typeRules);
    }
}
