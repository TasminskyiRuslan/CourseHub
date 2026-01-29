<?php

namespace App\Http\Requests\Api\Courses;

use App\Enums\CourseType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:courses,slug'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::enum(CourseType::class)],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
        ];
    }
}
