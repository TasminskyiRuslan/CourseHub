<?php

namespace App\Http\Requests\Api;

use App\Enums\CourseType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
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

        if ($this->has('description')) {
            $data['description'] = $this->string('description')->trim()->toString() ?: null;
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'present',
                'nullable',
                'string',
                'max:255',
                Rule::unique('courses', 'slug')->ignore($this->route('course')),
            ],
            'description' => ['present', 'nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
        ];
    }
}
