<?php

namespace App\Data\Lesson;

use App\Enums\CourseType;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class UpdateLessonData extends Data
{
    /**
     * @param string|Optional $title
     * @param string|Optional $slug
     * @param int|Optional $position
     * @param CarbonImmutable|Optional|null $start_time
     * @param CarbonImmutable|Optional|null $end_time
     * @param string|Optional|null $address
     * @param string|Optional|null $room_number
     * @param string|Optional|null $meeting_link
     * @param string|Optional|null $video_url
     * @param string|Optional|null $provider
     */
    public function __construct(
        public string|Optional               $title,

        public string|Optional               $slug,

        public int|Optional                  $position,

        public CarbonImmutable|Optional|null $start_time,

        public CarbonImmutable|Optional|null $end_time,

        public string|Optional|null          $address,

        public string|Optional|null          $room_number,

        public string|Optional|null          $meeting_link,

        public string|Optional|null          $video_url,

        public string|Optional|null          $provider,
    )
    {
    }

    /**
     * Return validation rules.
     *
     * @param ValidationContext|null $context
     * @return array
     */
    public static function rules(?ValidationContext $context = null): array
    {
        $lesson = request()->route('lesson');

        $rules = [
            'title' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('lessons', 'slug')
                    ->where('course_id', $lesson->course_id)
                    ->ignore($lesson),
            ],
            'position' => [
                'sometimes',
                'integer',
                'min:0',
            ]
        ];

        return match ($lesson->course->type) {
            CourseType::OFFLINE => array_merge($rules, [
                'start_time' => ['sometimes', 'nullable', 'date'],
                'end_time' => ['sometimes', 'nullable', 'date', 'after:start_time'],
                'address' => ['sometimes', 'nullable', 'string', 'max:255'],
                'room_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            ]),
            CourseType::ONLINE => array_merge($rules, [
                'start_time' => ['sometimes', 'nullable', 'date'],
                'end_time' => ['sometimes', 'nullable', 'date', 'after:start_time'],
                'meeting_link' => ['sometimes', 'nullable', 'url', 'max:2048'],
            ]),
            CourseType::VIDEO => array_merge($rules, [
                'video_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
                'provider' => ['sometimes', 'nullable', 'string', 'max:50'],
            ]),
            default => $rules,
        };
    }
}
