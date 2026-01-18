<?php

namespace App\Http\Requests\Api;

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLessonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', [Lesson::class, $this->course()]);
    }

    public function rules(): array
    {
        $course = $this->course();

        $isOffline = $course->type === CourseType::OFFLINE;
        $isOnline  = $course->type === CourseType::ONLINE;
        $isVideo   = $course->type === CourseType::VIDEO;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:lessons,slug'],
            'position' => ['nullable', 'integer', 'min:0'],

            'start_time' => [
                Rule::requiredIf($isOffline || $isOnline),
                Rule::prohibitedIf(! ($isOffline || $isOnline)),
                'date',
                'after_or_equal:today',
            ],

            'end_time' => [
                Rule::requiredIf($isOffline || $isOnline),
                Rule::prohibitedIf(! ($isOffline || $isOnline)),
                'date',
                'after:start_time',
            ],

            'address' => [
                Rule::requiredIf($isOffline),
                Rule::prohibitedIf(! $isOffline),
                'string',
                'max:255',
            ],

            'room_number' => [
                Rule::prohibitedIf(! $isOffline),
                'nullable',
                'string',
                'max:50',
            ],

            'meeting_link' => [
                Rule::requiredIf($isOnline),
                Rule::prohibitedIf(! $isOnline),
                'url',
                'max:2048',
            ],

            'video_url' => [
                Rule::requiredIf($isVideo),
                Rule::prohibitedIf(! $isVideo),
                'url',
                'max:2048',
            ],

            'provider' => [
                Rule::requiredIf($isVideo),
                Rule::prohibitedIf(! $isVideo),
                'string',
                'max:50',
            ],
        ];
    }

    protected function course(): Course
    {
        return $this->route('course');
    }

}
