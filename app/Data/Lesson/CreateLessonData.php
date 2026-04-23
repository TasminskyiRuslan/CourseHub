<?php

namespace App\Data\Lesson;

use App\Enums\CourseType;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class CreateLessonData extends Data
{
    /**
     * @param string $title
     * @param string|null $slug
     * @param int|null $position
     * @param CarbonImmutable|null $start_time
     * @param CarbonImmutable|null $end_time
     * @param string|null $address
     * @param string|null $room_number
     * @param string|null $meeting_link
     * @param string|null $video_url
     * @param string|null $provider
     */
    public function __construct(
        public string           $title,

        public ?string          $slug,

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

        $rules = [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('lessons', 'slug')->where('course_id', $course->id),
            ],
            'position' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];

        return match ($course->type) {
            CourseType::OFFLINE => array_merge($rules, [
                'start_time' => ['nullable', 'date', 'after_or_equal:today'],
                'end_time' => ['nullable', 'date', 'after:start_time'],
                'address' => ['nullable', 'string', 'max:255'],
                'room_number' => ['nullable', 'string', 'max:50'],
            ]),
            CourseType::ONLINE => array_merge($rules, [
                'start_time' => ['nullable', 'date', 'after_or_equal:today'],
                'end_time' => ['nullable', 'date', 'after:start_time'],
                'meeting_link' => ['nullable', 'url', 'max:2048'],
            ]),
            CourseType::VIDEO => array_merge($rules, [
                'video_url' => ['nullable', 'url', 'max:2048'],
                'provider' => ['nullable', 'string', 'max:50'],
            ]),
            default => $rules,
        };
    }
}
