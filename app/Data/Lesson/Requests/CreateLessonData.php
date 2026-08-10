<?php

declare(strict_types=1);

namespace App\Data\Lesson\Requests;

use App\Enums\CourseType;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class CreateLessonData extends Data
{
    public function __construct(
        public string $title,
        public ?string $slug,
        public ?int $position,
        public ?CarbonImmutable $start_time,
        public ?CarbonImmutable $end_time,
        public ?string $address,
        public ?string $room_number,
        public ?string $meeting_link,
        public ?string $video_url,
        public ?string $provider,
    ) {}

    /**
     * Return validation rules.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(ValidationContext $context): array
    {
        $teacherCourse = Route::current()?->parameter('teacherCourse');

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
                Rule::unique('lessons', 'slug')->where('course_id', $teacherCourse?->id),
            ],
            'position' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];

        if (! $teacherCourse) {
            return $rules;
        }

        return match ($teacherCourse->type) {
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
