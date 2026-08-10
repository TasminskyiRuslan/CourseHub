<?php

declare(strict_types=1);

namespace App\Data\Lesson\Requests;

use App\Enums\CourseType;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class UpdateLessonData extends Data
{
    public function __construct(
        public string|Optional $title,
        public string|Optional $slug,
        public int|Optional $position,
        public CarbonImmutable|Optional|null $start_time,
        public CarbonImmutable|Optional|null $end_time,
        public string|Optional|null $address,
        public string|Optional|null $room_number,
        public string|Optional|null $meeting_link,
        public string|Optional|null $video_url,
        public string|Optional|null $provider,
    ) {}

    /**
     * Return validation rules.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(ValidationContext $context): array
    {
        $teacherLesson = Route::current()?->parameter('teacherLesson');

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
                    ->where('course_id', $teacherLesson?->course_id)
                    ->ignore($teacherLesson),
            ],
            'position' => [
                'sometimes',
                'integer',
                'min:0',
            ],
        ];

        if (! $teacherLesson) {
            return $rules;
        }

        return match ($teacherLesson->course->type) {
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
