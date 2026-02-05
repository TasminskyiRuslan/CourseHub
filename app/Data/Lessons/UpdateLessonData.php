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
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class UpdateLessonData extends Data
{
    public function __construct(
        #[Required]
        #[StringType]
        #[Max(255)]
        public string           $title,

        #[Required]
        #[StringType]
        #[Max(255)]
        #[WithCast(SlugCast::class)]
        public string           $slug,

        #[Nullable]
        #[Present]
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
        $lesson = request()->route('lesson');
        $type = $lesson->course->type;

        $rules = [
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lessons', 'slug')
                    ->where('course_id', $lesson->course_id)
                    ->ignore($lesson),
            ],
        ];

        $typeRules = match ($type) {
            CourseType::OFFLINE => [
                'start_time' => ['present', 'nullable', 'date'],
                'end_time' => ['present', 'nullable', 'date', 'after:start_time'],
                'address' => ['present', 'nullable', 'string', 'max:255'],
                'room_number' => ['present', 'nullable', 'string', 'max:50'],
            ],
            CourseType::ONLINE => [
                'start_time' => ['present', 'nullable', 'date'],
                'end_time' => ['present', 'nullable', 'date', 'after:start_time'],
                'meeting_link' => ['present', 'nullable', 'url', 'max:2048'],
            ],
            CourseType::VIDEO => [
                'video_url' => ['present', 'nullable', 'url', 'max:2048'],
                'provider' => ['present', 'nullable', 'string', 'max:50'],
            ],
        };

        return array_merge($rules, $typeRules);
    }
}
