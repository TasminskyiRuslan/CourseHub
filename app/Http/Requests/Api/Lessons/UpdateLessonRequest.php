<?php

namespace App\Http\Requests\Api\Lessons;

use App\Enums\CourseType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('slug'))) {
            $this->merge([
                'slug' => Str::slug($this->input('slug')),
            ]);
        }
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return array_merge($this->commonRules(), $this->typeRules());
    }

    private function commonRules(): array
    {
        $lesson = $this->route('lesson');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lessons', 'slug')
                    ->where('course_id', $lesson->course_id)
                    ->ignore($lesson),
            ],
            'position' => ['present', 'nullable', 'integer', 'min:0'],
        ];
    }

    private function typeRules(): array
    {
        $lesson = $this->route('lesson');

        return match ($lesson->course->type) {
            CourseType::OFFLINE => $this->offlineRules(),
            CourseType::ONLINE  => $this->onlineRules(),
            CourseType::VIDEO   => $this->videoRules(),
        };
    }

    private function offlineRules(): array
    {
        return [
            'start_time' => ['present', 'nullable', 'date'],
            'end_time' => ['present', 'nullable', 'date', 'after:start_time'],
            'address' => ['present', 'nullable', 'string', 'max:255'],
            'room_number' => ['present', 'nullable', 'string', 'max:50'],
        ];
    }

    private function onlineRules(): array
    {
        return [
            'start_time'   => ['present', 'nullable', 'date'],
            'end_time'     => ['present', 'nullable', 'date', 'after:start_time'],
            'meeting_link' => ['present', 'nullable', 'url', 'max:2048'],
        ];
    }

    private function videoRules(): array
    {
        return [
            'video_url' => ['present', 'nullable', 'url', 'max:2048'],
            'provider'  => ['present', 'nullable', 'string', 'max:50'],
        ];
    }
}
