<?php

namespace App\Http\Requests\Api;

use App\Enums\CourseType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLessonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('title')) {
            $data['title'] = $this->string('title')->trim()->toString();
        }

        if ($this->has('slug')) {
            $data['slug'] = $this->string('slug')->trim()->toString() ?: null;
        }

        if ($this->has('position')) {
            $data['position'] = $this->filled('position') ? $this->integer('position') : null;
        }

        if ($this->has('address')) {
            $data['address'] = $this->string('address')->trim()->toString() ?: null;
        }

        if ($this->has('room_number')) {
            $data['room_number'] = $this->string('room_number')->trim()->toString() ?: null;
        }

        if ($this->has('meeting_link')) {
            $data['meeting_link'] = $this->string('meeting_link')->trim()->toString() ?: null;
        }

        if ($this->has('video_url')) {
            $data['video_url'] = $this->string('video_url')->trim()->toString() ?: null;
        }

        if ($this->has('provider')) {
            $data['provider'] = $this->string('provider')->trim()->toString() ?: null;
        }

        $this->merge($data);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $lesson = $this->route('lesson');
        $course = $lesson->course;

        $isOffline = $course->type === CourseType::OFFLINE;
        $isOnline = $course->type === CourseType::ONLINE;
        $isVideo = $course->type === CourseType::VIDEO;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'present',
                'nullable',
                'string',
                'max:255',
                Rule::unique('lessons', 'slug')
                    ->where('course_id', $lesson->course_id)
                    ->ignore($lesson)
            ],
            'position' => ['present', 'nullable', 'integer', 'min:0'],
            'start_time' => [
                $isOffline || $isOnline ? 'present' : 'prohibited',
                'nullable',
                'date',
            ],
            'end_time' => [
                $isOffline || $isOnline ? 'present' : 'prohibited',
                'nullable',
                'date',
                'after:start_time',
            ],
            'address' => [
                $isOffline ? 'present' : 'prohibited',
                'nullable',
                'string',
                'max:255',
            ],
            'room_number' => [
                $isOffline ? 'present' : 'prohibited',
                'nullable',
                'string',
                'max:50',
            ],
            'meeting_link' => [
                $isOnline ? 'present' : 'prohibited',
                'nullable',
                'url',
                'max:2048',
            ],
            'video_url' => [
                $isVideo ? 'present' : 'prohibited',
                'nullable',
                'url',
                'max:2048',
            ],
            'provider' => [
                $isVideo ? 'present' : 'prohibited',
                'nullable',
                'string',
                'max:50',
            ],
        ];
    }
}
